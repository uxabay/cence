<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lab_sample_categories', function (Blueprint $table) {
            // Προσθήκη πεδίων για έλεγχο συμμετοχής στα στατιστικά
            $table->boolean('is_counted_in_lab')
                ->default(true)
                ->after('status')
                ->comment('Αν η κατηγορία μετρά στα στατιστικά του εργαστηρίου');

            $table->boolean('is_counted_in_contract')
                ->default(true)
                ->after('is_counted_in_lab')
                ->comment('Αν η κατηγορία μετρά στα στατιστικά των συμβάσεων');

            $table->boolean('is_virtual')
                ->default(false)
                ->after('is_counted_in_contract')
                ->comment('Αν η κατηγορία είναι εικονική / διοικητική');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_sample_categories', function (Blueprint $table) {
            $table->dropColumn(['is_counted_in_lab', 'is_counted_in_contract', 'is_virtual']);
        });
    }
};
