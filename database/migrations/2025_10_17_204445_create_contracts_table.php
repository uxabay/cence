<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique();
            $table->string('title', 255);
            $table->text('subject')->nullable();

            // Enums
            $table->string('contract_type', 30)->default('programmatiki');
            $table->string('status', 30)->default('draft');

            // Relations
            $table->foreignId('lab_customer_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('contracts')->nullOnDelete();

            // Basic fields
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('total_value', 12, 2)->default(0);
            $table->string('funding_source', 255)->nullable();
            $table->string('scope', 255)->nullable();
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
        Schema::dropIfExists('contracts');
    }
};
