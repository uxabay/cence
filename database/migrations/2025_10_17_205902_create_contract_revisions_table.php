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

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->integer('revision_number')->default(1);
            $table->date('revision_date')->nullable();

            // Enum: τύπος αναθεώρησης
            $table->string('type', 30)->default('financial');

            $table->text('description')->nullable();
            $table->decimal('amount_change', 12, 2)->default(0);
            $table->decimal('new_total_value', 12, 2)->default(0);

            // Optional file attachment
            $table->foreignId('file_attachment_id')->nullable()
                ->constrained('file_attachments')
                ->nullOnDelete();

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
        Schema::dropIfExists('contract_revisions');
    }
};
