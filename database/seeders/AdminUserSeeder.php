<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Αν υπάρχει ήδη admin user, τον ενημερώνει
        $admin = User::firstOrCreate(
            ['email' => 'admin@cence.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'), // άλλαξέ το αργότερα!
                'status' => 'active',
                'force_password_reset' => true,
            ]
        );

        // Ανάθεση ρόλου Administrator
        $role = Role::where('name', 'Administrator')->first();
        if ($role && !$admin->hasRole($role->name)) {
            $admin->assignRole($role);
        }

        // Προαιρετικά, ενημέρωση audit fields
        if (method_exists($admin, 'saveQuietly')) {
            $admin->saveQuietly();
        } else {
            $admin->save();
        }

        $this->command->info('✅ Administrator user created or updated.');
    }
}
