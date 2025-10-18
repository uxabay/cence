<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sample_reallocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreignId('contract_year_id')
                ->nullable()
                ->constrained('contract_years')
                ->cascadeOnUpdate()
                ->nullOnDelete();

            $table->foreignId('from_sample_type_id')
                ->constrained('contract_sample_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('to_sample_type_id')
                ->constrained('contract_sample_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->integer('samples_moved')->default(0);
            $table->decimal('amount_moved', 12, 2)->default(0);

            $table->text('rationale')->nullable();

            // Σύνδεση με πιθανή αναθεώρηση
            $table->foreignId('contract_revision_id')
                ->nullable()
                ->constrained('contract_revisions')
                ->nullOnDelete();

            // Έγκριση
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sample_reallocations');
    }
};
