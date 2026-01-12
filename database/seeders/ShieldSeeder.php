<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"Administrator","guard_name":"web","permissions":["view_any_customer_category","view_customer_category","create_customer_category","update_customer_category","delete_customer_category","restore_customer_category","force_delete_customer_category","force_delete_any_customer_category","restore_any_customer_category","replicate_customer_category","reorder_customer_category","view_any_lab_customer","view_lab_customer","create_lab_customer","update_lab_customer","delete_lab_customer","restore_lab_customer","force_delete_lab_customer","force_delete_any_lab_customer","restore_any_lab_customer","replicate_lab_customer","reorder_lab_customer","view_any_lab_sample_category","view_lab_sample_category","create_lab_sample_category","update_lab_sample_category","delete_lab_sample_category","restore_lab_sample_category","force_delete_lab_sample_category","force_delete_any_lab_sample_category","restore_any_lab_sample_category","replicate_lab_sample_category","reorder_lab_sample_category","view_any_role","view_role","create_role","update_role","delete_role","restore_role","force_delete_role","force_delete_any_role","restore_any_role","replicate_role","reorder_role","view_any_user","view_user","create_user","update_user","delete_user","restore_user","force_delete_user","force_delete_any_user","restore_any_user","replicate_user","reorder_user","view_dashboard","manage_roles","manage_financials","view_revisions","create_revisions","approve_revisions","manage_reallocations","view_related_registrations","view_any_contract_sample_category","view_contract_sample_category","create_contract_sample_category","update_contract_sample_category","delete_contract_sample_category","restore_contract_sample_category","force_delete_contract_sample_category","force_delete_any_contract_sample_category","restore_any_contract_sample_category","replicate_contract_sample_category","reorder_contract_sample_category","view_any_contract","view_contract","create_contract","update_contract","delete_contract","restore_contract","force_delete_contract","force_delete_any_contract","restore_any_contract","replicate_contract","reorder_contract","view_any_registration","view_registration","create_registration","update_registration","delete_registration","restore_registration","force_delete_registration","force_delete_any_registration","restore_any_registration","replicate_registration","reorder_registration","view_any_lab_analysis","view_lab_analysis","create_lab_analysis","update_lab_analysis","delete_lab_analysis","restore_lab_analysis","force_delete_lab_analysis","force_delete_any_lab_analysis","restore_any_lab_analysis","replicate_lab_analysis","reorder_lab_analysis","view_any_lab_analysis_package","view_lab_analysis_package","create_lab_analysis_package","update_lab_analysis_package","delete_lab_analysis_package","restore_lab_analysis_package","force_delete_lab_analysis_package","force_delete_any_lab_analysis_package","restore_any_lab_analysis_package","replicate_lab_analysis_package","reorder_lab_analysis_package","view_contract_notification_settings_page","view_stats_overview","view_sample_intake_stacked_chart","view_customer_category_sample_distribution_chart","view_activity_log"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
