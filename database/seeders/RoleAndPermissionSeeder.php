<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 🔹 Καθαρισμός προηγούμενων δεδομένων
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::query()->delete();
        Role::query()->delete();

        // ------------------------------------------------------------
        // 1️⃣ Βασικές ομάδες permissions (CRUD + import/export + soft deletes)
        // ------------------------------------------------------------
        $entities = [
            'contracts',
            'contract_sample_types',
            'lab_sample_types',
            'lab_customers',
            'registrations',
            'contract_sample_revisions',
            'contract_warnings',
            'reports',
            'attachments',
        ];

        $baseActions = [
            'view_any', 'view', 'create', 'update',
            'delete', 'restore', 'force_delete',
            'import', 'export',
        ];

        foreach ($entities as $entity) {
            foreach ($baseActions as $action) {
                Permission::firstOrCreate(['name' => "{$action}_{$entity}"]);
            }
        }

        // ------------------------------------------------------------
        // 2️⃣ Ειδικά system-level permissions
        // ------------------------------------------------------------
        $systemPermissions = [
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'view_activity_log',
            'view_settings',
            'update_settings',
            'view_job_batches',
            'manage_notifications',
            'manage_revisions',
            'manage_warnings',
        ];

        foreach ($systemPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // ------------------------------------------------------------
        // 3️⃣ Δημιουργία Ρόλων
        // ------------------------------------------------------------
        $roles = [
            'Administrator' => [
                // πλήρη πρόσβαση σε όλα
                Permission::all()->pluck('name')->toArray(),
            ],

            'Manager' => [
                // πλήρη σε όλα τα business αντικείμενα εκτός από system
                // έχει δικαίωμα αναμορφώσεων & warnings
                ...collect(Permission::where(function ($q) {
                    $q->where('name', 'like', '%contracts%')
                        ->orWhere('name', 'like', '%contract_sample_types%')
                        ->orWhere('name', 'like', '%lab_customers%')
                        ->orWhere('name', 'like', '%registrations%')
                        ->orWhere('name', 'like', '%contract_sample_revisions%')
                        ->orWhere('name', 'like', '%contract_warnings%')
                        ->orWhere('name', 'like', '%reports%')
                        ->orWhere('name', 'like', '%attachments%');
                })->pluck('name'))->toArray(),
                'manage_revisions',
                'manage_warnings',
            ],

            'Operator' => [
                // καταχώριση πρωτοκόλλων (registrations) και προβολή συμβάσεων/πελατών
                'view_any_contracts',
                'view_contracts',
                'view_any_lab_customers',
                'view_lab_customers',
                'view_any_lab_sample_types',
                'view_lab_sample_types',
                'create_registrations',
                'update_registrations',
                'delete_registrations',
                'restore_registrations',
                'import_registrations',
                'export_registrations',
                'view_reports',
                'export_reports',
            ],

            'Viewer' => [
                // μόνο προβολή δεδομένων και αναφορών
                'view_any_contracts',
                'view_contracts',
                'view_any_lab_customers',
                'view_lab_customers',
                'view_any_lab_sample_types',
                'view_lab_sample_types',
                'view_any_registrations',
                'view_registrations',
                'view_any_reports',
                'view_reports',
            ],
        ];

        // ------------------------------------------------------------
        // 4️⃣ Εγγραφή Ρόλων & Permissions
        // ------------------------------------------------------------
        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($perms);
        }

        // ------------------------------------------------------------
        // 5️⃣ Επαναφορά cache Spatie
        // ------------------------------------------------------------
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Προαιρετικό μήνυμα για debug
        $this->command->info('✅ Roles & Permissions created successfully.');
    }
}
