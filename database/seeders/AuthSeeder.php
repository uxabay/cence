<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AuthSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        /**********************************************
         * 1️⃣ Ορισμός όλων των entities της εφαρμογής
         **********************************************/
        $entities = [
            // --- Core entities ---
            'contract',
            'contract_sample_type',
            'lab_sample_type',
            'lab_customer',
            'customer_category',
            'registration',
            'contract_sample_revision',
            'contract_warning',

            // --- System entities ---
            'user',
            'role',
            'permission',
        ];

        /**********************************************
         * 2️⃣ Τυπικές CRUD permissions
         **********************************************/
        $crud = [
            'view_any',
            'view',
            'create',
            'update',
            'delete',
            'restore',
            'force_delete',
            'import',
            'export',
        ];

        /**********************************************
         * 3️⃣ Domain-specific permissions
         **********************************************/
        $domain = [
            'manage_revisions',
            'manage_warnings',
        ];

        /**********************************************
         * 4️⃣ System-level permissions (εκτός CRUD)
         **********************************************/
        $system = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'view_activity_log',
            'view_job_batches',
            'manage_notifications',
            'view_settings',
            'update_settings',
        ];

        /**********************************************
         * 5️⃣ Δημιουργία όλων των permissions
         **********************************************/
        foreach ($entities as $entity) {
            foreach ($crud as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action}_{$entity}",
                    'guard_name' => $guard,
                ]);
            }
        }

        foreach ($domain as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        foreach ($system as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard,
            ]);
        }

        /**********************************************
         * 6️⃣ Δημιουργία ρόλων
         **********************************************/
        $admin    = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => $guard]);
        $manager  = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => $guard]);
        $operator = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => $guard]);
        $viewer   = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => $guard]);

        /**********************************************
         * 7️⃣ Ανάθεση δικαιωμάτων ανά ρόλο
         **********************************************/

        // 🔹 Administrator: όλα
        $admin->syncPermissions(Permission::all());

        // 🔹 Manager: όλα εκτός system-level
        $manager->syncPermissions(
            Permission::whereNotIn('name', [
                'manage_users',
                'manage_roles',
                'manage_permissions',
                'update_settings',
            ])->get()
        );

        // 🔹 Operator: περιορισμένο λειτουργικό σύνολο
        $operatorPermissions = [
            // Contracts
            'view_any_contract','view_contract','create_contract','update_contract','import_contract','export_contract',
            // Registrations
            'view_any_registration','view_registration','create_registration','update_registration','import_registration','export_registration',
            // Customers
            'view_any_lab_customer','view_lab_customer',
            // Sample types
            'view_any_lab_sample_type','view_lab_sample_type',
            // Categories
            'view_any_customer_category','view_customer_category',
            // Warnings/Revisions (μόνο προβολή)
            'view_any_contract_warning','view_contract_warning','view_any_contract_sample_revision','view_contract_sample_revision',
        ];
        $operator->syncPermissions(Permission::whereIn('name', $operatorPermissions)->get());

        // 🔹 Viewer: μόνο προβολή (view, view_any)
        $viewer->syncPermissions(Permission::where(function($q){
            $q->where('name','like','view_any_%')->orWhere('name','like','view_%');
        })->get());

        /**********************************************
         * 8️⃣ Επαναφορά cache
         **********************************************/
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
