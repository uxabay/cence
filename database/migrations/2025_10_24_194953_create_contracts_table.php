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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();

            $table->string('title', 255);
            $table->foreignId('lab_customer_id')->constrained('lab_customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();
            $table->string('contract_number', 100)->nullable();
            $table->text('description')->nullable();

            // Αρχειοθέτηση & κατάσταση
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->foreignId('file_attachment_id')->nullable()->constrained('file_attachments')->nullOnDelete();
            $table->text('remarks')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            // System fields
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('uuid')->nullable()->unique();

            $table->index(['lab_customer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
