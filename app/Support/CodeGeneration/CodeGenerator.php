<?php

declare(strict_types=1);

namespace App\Support\CodeGeneration;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

final class CodeGenerator
{
    public function next(string $modelClass, string $column, ?string $prefix = null): string
    {
        /** @var Model $model */
        $model = new $modelClass;

        $prefix = $prefix !== null ? (string) $prefix : null;

        $lockName = $this->lockName($modelClass, $column, $prefix);

        return $this->withAdvisoryLock($lockName, function () use ($model, $column, $prefix): string {
            // retry loop (extra safety)
            $attempts = 5;

            while ($attempts-- > 0) {
                [$base, $number, $width] = $this->resolveBaseAndMax($model, $column, $prefix);

                $nextNumber = $number + 1;

                $tail = $width > 0
                    ? str_pad((string) $nextNumber, $width, '0', STR_PAD_LEFT)
                    : (string) $nextNumber;

                $candidate = ($base ?? '') . $tail;

                if (! $this->exists($model, $column, $candidate)) {
                    return $candidate;
                }
            }

            throw new \RuntimeException('Code generation failed due to repeated collisions.');
        });
    }

    private function resolveBaseAndMax(Model $model, string $column, ?string $prefix): array
    {
        // PREFIX MODE (best-case): we can compute max numeric tail in SQL
        if ($prefix !== null && $prefix !== '') {
            $len = mb_strlen($prefix);

            $query = $model->newQuery()
                ->select($column)
                ->where($column, 'like', $prefix . '%');

            // exclude soft deleted if model supports it
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model), true)) {
                $query->whereNull($model->getDeletedAtColumn());
            }

            // tail part must be digits only (after prefix)
            $tailExpr = "SUBSTRING($column, " . ($len + 1) . ")";
            $maxTail = (int) ($query->clone()
                ->whereRaw("$tailExpr REGEXP '^[0-9]+$'")
                ->max(DB::raw("CAST($tailExpr AS UNSIGNED)")) ?? 0);

            // width: preserve leading zeros based on the record that contains the maxTail
            $maxCode = $query->clone()
                ->whereRaw("$tailExpr REGEXP '^[0-9]+$'")
                ->whereRaw("CAST($tailExpr AS UNSIGNED) = ?", [$maxTail])
                ->orderBy($column, 'desc')
                ->value($column);

            $width = 0;
            if (is_string($maxCode) && str_starts_with($maxCode, $prefix)) {
                $tail = substr($maxCode, strlen($prefix));
                $width = (ctype_digit($tail) && $tail !== '') ? strlen($tail) : 0;
            }

            return [$prefix, $maxTail, $width];
        }

        // NO PREFIX MODE: find the max trailing number by sampling recent records and parsing in PHP
        // (portable + safe; acceptable since no hard patterns allowed anyway)
        $query = $model->newQuery()->select($column);

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model), true)) {
            $query->whereNull($model->getDeletedAtColumn());
        }

        // grab a reasonable window; ordering by PK is usually indexed
        $rows = $query->orderByDesc($model->getKeyName())->limit(2000)->pluck($column);

        $bestNumber = 0;
        $bestBase = '';
        $bestWidth = 0;

        foreach ($rows as $value) {
            if (! is_string($value) || $value === '') {
                continue;
            }

            if (! preg_match('/(\d+)$/u', $value, $m)) {
                continue;
            }

            $tail = $m[1];
            $base = substr($value, 0, -strlen($tail));

            $num = (int) $tail;
            if ($num > $bestNumber) {
                $bestNumber = $num;
                $bestBase = $base;
                $bestWidth = strlen($tail);
            }
        }

        return [$bestBase, $bestNumber, $bestWidth];
    }

    private function exists(Model $model, string $column, string $value): bool
    {
        $q = $model->newQuery()->where($column, $value);

        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($model), true)) {
            $q->whereNull($model->getDeletedAtColumn());
        }

        return $q->exists();
    }

    private function lockName(string $modelClass, string $column, ?string $prefix): string
    {
        $key = $modelClass . '|' . $column . '|' . ($prefix ?? '');
        // MySQL lock name max length is 64; hash it
        return 'codegen:' . substr(sha1($key), 0, 52);
    }

    private function withAdvisoryLock(string $name, \Closure $fn): mixed
    {
        return DB::transaction(function () use ($name, $fn) {
            $acquired = (int) (DB::selectOne('SELECT GET_LOCK(?, 10) AS l', [$name])->l ?? 0);

            if ($acquired !== 1) {
                throw new \RuntimeException('Could not acquire code generation lock.');
            }

            try {
                return $fn();
            } finally {
                DB::select('SELECT RELEASE_LOCK(?)', [$name]);
            }
        });
    }
}
