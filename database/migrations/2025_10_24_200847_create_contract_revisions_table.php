<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_revisions', function (Blueprint $table) {
            $table->id();

            // Συσχετίσεις
            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('from_sample_id')
                ->nullable()
                ->constrained('contract_samples')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('to_sample_id')
                ->nullable()
                ->constrained('contract_samples')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Πεδία αναδιανομής
            $table->integer('num_of_samples')->default(0);
            $table->decimal('amount_delta', 10, 2)->nullable();

            // Ημερομηνία & έτος
            $table->date('date')->nullable();
            $table->smallInteger('year')->unsigned()->nullable();

            // Σχόλια / σημειώσεις
            $table->text('notes')->nullable();

            // Κατάσταση (ενεργή ή όχι)
            $table->enum('status', ['active', 'inactive'])->default('active');

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
            $table->index(['contract_id']);
            $table->index(['year']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_revisions');
    }
};
