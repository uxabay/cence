<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        // 1️⃣ Ρόλοι & δικαιώματα (πλήρες σετάρισμα)
        $this->call(AuthSeeder::class);

        // 2️⃣ Αρχικός διαχειριστής (admin user)
        $this->call(AdminUserSeeder::class);
    }
}
