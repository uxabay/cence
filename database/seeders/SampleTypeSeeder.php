<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SampleType;
use App\Enums\RecordStatusEnum;
use Illuminate\Support\Facades\Auth;

class SampleTypeSeeder extends Seeder
{
    public function run(): void
    {
        // Αν δεν υπάρχει Auth user (όπως σε fresh install), χρησιμοποιούμε τον User με ID=1
        $userId = Auth::id() ?? 1;

        $sampleTypes = [
            [
                'name' => 'Νερά',
                'code' => 'WAT',
                'description' => 'Δείγματα νερού (πόσιμο, υπόγειο, επιφανειακό, θάλασσα, πισίνες).',
                'status' => RecordStatusEnum::Active,
            ],
            [
                'name' => 'Τρόφιμα',
                'code' => 'FOO',
                'description' => 'Δείγματα τροφίμων και πρώτων υλών τροφίμων.',
                'status' => RecordStatusEnum::Active,
            ],
            [
                'name' => 'Λύματα',
                'code' => 'WAS',
                'description' => 'Δείγματα αστικών και βιομηχανικών λυμάτων, ιλύος, αποβλήτων.',
                'status' => RecordStatusEnum::Active,
            ],
            [
                'name' => 'Κλινικά Δείγματα',
                'code' => 'CLI',
                'description' => 'Κλινικά και βιολογικά δείγματα για μοριακές ή μικροβιολογικές αναλύσεις.',
                'status' => RecordStatusEnum::Active,
            ],
        ];

        foreach ($sampleTypes as $data) {
            SampleType::updateOrCreate(
                ['code' => $data['code']],
                array_merge($data, [
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ])
            );
        }
    }
}
