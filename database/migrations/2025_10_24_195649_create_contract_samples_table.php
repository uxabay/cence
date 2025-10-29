<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_samples', function (Blueprint $table) {
            $table->id();

            // Συσχετίσεις
            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('contract_sample_category_id')
                ->constrained('contract_sample_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Βασικά πεδία
            $table->string('name', 255);
            $table->boolean('is_master')->default(true);
            $table->year('year')->nullable();

            // Οικονομικά δεδομένα
            $table->integer('forecasted_samples')->default(0);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('forecasted_amount', 12, 2)->default(0);
            $table->string('currency_code', 10)->default('EUR');

            // Πρόσθετα
            $table->text('remarks')->nullable();

            // Κατάσταση
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Audit
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
            $table->index(['contract_id', 'contract_sample_category_id']);
            $table->index(['year']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_samples');
    }
};
