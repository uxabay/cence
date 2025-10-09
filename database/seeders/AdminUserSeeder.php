<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Δημιουργία ρόλου Administrator
        $role = Role::firstOrCreate(['name' => 'Administrator']);

        // Δημιουργία permission wildcard (προαιρετικά, αν θέλεις «τα πάντα»)
        Permission::firstOrCreate(['name' => '*']);
        $role->givePermissionTo('*');

        // Δημιουργία admin χρήστη
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password')]
        );

        $admin->assignRole('Administrator');
    }
}
