<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lab;
use App\Enums\RecordStatusEnum;
use Illuminate\Support\Facades\Auth;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        // Αν δεν υπάρχει authenticated user (π.χ. σε fresh install),
        // χρησιμοποιούμε user με ID=1 ως default δημιουργό.
        $userId = Auth::id() ?? 1;

        $labs = [
            [
                'name' => 'Μικροβιολογικό Εργαστήριο',
                'code' => 'MB',
                'description' => 'Αναλύσεις μικροβιολογίας νερού, τροφίμων και περιβαλλοντικών δειγμάτων.',
                'status' => RecordStatusEnum::Active,
            ],
            [
                'name' => 'Χημικό Εργαστήριο',
                'code' => 'CH',
                'description' => 'Χημικές αναλύσεις νερού, εδάφους, ιλύος και άλλων δειγμάτων.',
                'status' => RecordStatusEnum::Active,
            ],
            [
                'name' => 'Μοριακό Εργαστήριο',
                'code' => 'MO',
                'description' => 'Μοριακές και PCR αναλύσεις για παθογόνα, ιούς και γενετικούς δείκτες.',
                'status' => RecordStatusEnum::Active,
            ],
        ];

        foreach ($labs as $labData) {
            Lab::updateOrCreate(
                ['code' => $labData['code']],
                array_merge($labData, [
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ])
            );
        }
    }
}
