<?php

declare(strict_types=1);

namespace App\Support\CodeGeneration;

final class CodeParser
{
    public function parse(?string $input): ParsedCode
    {
        $input = trim((string) $input);

        // empty => not auto (leave it empty)
        if ($input === '') {
            return new ParsedCode(false, null);
        }

        // "*" => auto without prefix
        if ($input === '*') {
            return new ParsedCode(true, null);
        }

        // suffix "*" => auto with prefix resolved
        if (str_ends_with($input, '*')) {
            $prefix = rtrim($input, '*');

            return new ParsedCode(true, $prefix === '' ? null : $prefix);
        }

        // normal value => user-provided, keep as-is
        return new ParsedCode(false, null);
    }
}
