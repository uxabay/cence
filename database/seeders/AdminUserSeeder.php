<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Enums\UserStatus;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1️⃣ Δημιουργία ή εύρεση ρόλου Administrator
        $adminRole = Role::firstOrCreate(
            [
                'name' => 'Administrator',
                'guard_name' => 'web',
            ]
        );

        // 2️⃣ Δημιουργία ή ενημέρωση χρήστη admin
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@cence.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'status' => UserStatus::ACTIVE,
                'force_password_reset' => false,
            ]
        );

        // 3️⃣ Αν δεν έχει τον ρόλο Administrator, τον προσθέτουμε
        if (!$adminUser->hasRole('Administrator')) {
            $adminUser->assignRole($adminRole);
        }

        // 4️⃣ Προαιρετικά, δίνουμε όλα τα permissions του Filament Shield
        try {
            if (class_exists(\BezhanSalleh\FilamentShield\Models\Permission::class)) {
                $permissions = \Spatie\Permission\Models\Permission::pluck('name')->toArray();
                $adminRole->syncPermissions($permissions);
            }
        } catch (\Throwable $th) {
            $this->command->warn('⚠️ Shield permissions not found — skipping.');
        }

        // 5️⃣ Εμφάνιση ενημερωτικού μηνύματος
        $this->command->info('✅ Administrator role and admin user ensured.');
        $this->command->warn('➡️ Email: admin@cence.test | Password: password');
    }
}
