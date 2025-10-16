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
        // 2️⃣ Αρχικός διαχειριστής (admin user)
        $this->call(AdminUserSeeder::class);
        $this->call(LabSeeder::class);
        $this->call(SampleTypeSeeder::class);
    }
}
