<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sample_lab_category', function (Blueprint $table) {
            $table->id();

            // Συσχετίσεις
            $table->foreignId('contract_sample_id')
                ->constrained('contract_samples')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('lab_sample_category_id')
                ->constrained('lab_sample_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Οικονομικά δεδομένα (προαιρετική ειδική τιμή)
            $table->decimal('unit_price', 10, 2)->nullable();

            // Κατάσταση ενεργότητας
            $table->boolean('active')->default(true);

            // Audit fields
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // System fields
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('uuid')->nullable()->unique();

            // Indexes
            $table->unique(['contract_sample_id', 'lab_sample_category_id'], 'contract_lab_unique');
            $table->index(['active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sample_lab_category');
    }
};
