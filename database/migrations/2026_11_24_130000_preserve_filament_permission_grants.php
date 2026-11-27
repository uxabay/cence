<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Copy legacy permission assignments to the canonical keys without
     * deleting or renaming existing permission rows.
     */
    public function up(): void
    {
        $customPermissions = [
            'manage_roles',
            'manage_financials',
            'manage_notification_settings',
            'view_revisions',
            'create_revisions',
            'approve_revisions',
            'manage_reallocations',
            'view_related_registrations',
            'view_activity_log',
            'view_system_info',
        ];

        DB::transaction(function () use ($customPermissions): void {
            foreach ($customPermissions as $permission) {
                $this->copyPermissionAssignments(
                    sourceName: str($permission)->studly()->toString(),
                    targetName: $permission,
                );
            }

            // The statistics page previously checked this shared permission.
            // Copy its grants to the dedicated page permission to preserve access.
            $this->copyPermissionAssignments(
                sourceName: 'view_dashboard',
                targetName: 'view_sample_statistics',
            );
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Keep canonical permissions and grants in place when rolling back. Removing
     * copied grants could unexpectedly revoke access after the application rollback.
     */
    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function copyPermissionAssignments(string $sourceName, string $targetName): void
    {
        $permissionTable = config('permission.table_names.permissions');

        $sources = DB::table($permissionTable)
            ->where('name', $sourceName)
            ->get(['id', 'guard_name']);

        foreach ($sources as $source) {
            $targetId = DB::table($permissionTable)
                ->where('name', $targetName)
                ->where('guard_name', $source->guard_name)
                ->value('id');

            if (! $targetId) {
                $targetId = DB::table($permissionTable)->insertGetId([
                    'name' => $targetName,
                    'guard_name' => $source->guard_name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $this->copyRoleAssignments((int) $source->id, (int) $targetId);
            $this->copyDirectAssignments((int) $source->id, (int) $targetId);
        }
    }

    private function copyRoleAssignments(int $sourcePermissionId, int $targetPermissionId): void
    {
        $table = config('permission.table_names.role_has_permissions');
        $columns = config('permission.column_names');
        $permissionKey = $columns['permission_pivot_key'] ?? 'permission_id';
        $roleKey = $columns['role_pivot_key'] ?? 'role_id';

        DB::table($table)
            ->where($permissionKey, $sourcePermissionId)
            ->get([$roleKey])
            ->each(fn (object $assignment) => DB::table($table)->insertOrIgnore([
                $permissionKey => $targetPermissionId,
                $roleKey => $assignment->{$roleKey},
            ]));
    }

    private function copyDirectAssignments(int $sourcePermissionId, int $targetPermissionId): void
    {
        $table = config('permission.table_names.model_has_permissions');
        $columns = config('permission.column_names');
        $permissionKey = $columns['permission_pivot_key'] ?? 'permission_id';
        $modelKey = $columns['model_morph_key'] ?? 'model_id';
        $teamKey = $columns['team_foreign_key'] ?? 'team_id';
        $assignmentColumns = [$modelKey, 'model_type'];

        if (Schema::hasColumn($table, $teamKey)) {
            $assignmentColumns[] = $teamKey;
        }

        DB::table($table)
            ->where($permissionKey, $sourcePermissionId)
            ->get($assignmentColumns)
            ->each(function (object $assignment) use ($table, $permissionKey, $modelKey, $teamKey, $targetPermissionId): void {
                $values = [
                    $permissionKey => $targetPermissionId,
                    $modelKey => $assignment->{$modelKey},
                    'model_type' => $assignment->model_type,
                ];

                if (property_exists($assignment, $teamKey)) {
                    $values[$teamKey] = $assignment->{$teamKey};
                }

                DB::table($table)->insertOrIgnore($values);
            });
    }
};
