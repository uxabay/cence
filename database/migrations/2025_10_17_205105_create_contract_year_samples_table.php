<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_year_samples', function (Blueprint $table) {
            $table->id();

            // Συσχέτιση με έτος σύμβασης
            $table->foreignId('contract_year_id')
                ->constrained('contract_years')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Αν υπάρχει ήδη ο πίνακας contract_sample_types, φτιάξε FK, αλλιώς απλό πεδίο
            if (Schema::hasTable('contract_sample_types')) {
                $table->foreignId('contract_sample_type_id')
                    ->constrained('contract_sample_types')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            } else {
                // Δημιουργεί τη στήλη χωρίς constraint (ώστε να περάσει το migration)
                $table->unsignedBigInteger('contract_sample_type_id')->nullable()->index();
            }

            $table->integer('planned_samples')->default(0);
            $table->integer('executed_samples')->default(0);
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->text('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_year_samples');
    }
};
