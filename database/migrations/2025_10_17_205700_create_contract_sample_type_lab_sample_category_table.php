<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sample_type_lab_sample_category', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_sample_type_id')
                ->constrained('contract_sample_types', indexName: 'cst_id_fk')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('lab_sample_category_id')
                ->constrained('lab_sample_categories', indexName: 'lsc_id_fk')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->boolean('include_virtual')->default(false);
            $table->boolean('include_lab_count')->default(true);
            $table->boolean('include_contract_count')->default(true);
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(
                ['contract_sample_type_id', 'lab_sample_category_id'],
                'cst_lsc_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sample_type_lab_sample_category');
    }
};
