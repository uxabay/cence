<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_years', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->year('year');
            $table->decimal('annual_budget', 12, 2)->default(0);
            $table->integer('samples_planned')->default(0);
            $table->integer('samples_actual')->default(0);
            $table->decimal('amount_used', 12, 2)->default(0);
            $table->decimal('amount_remaining', 12, 2)->default(0);
            $table->text('remarks')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_years');
    }
};
