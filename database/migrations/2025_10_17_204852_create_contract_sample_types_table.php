<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sample_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('unit_cost', 10, 2)->default(0);
            $table->integer('planned_samples')->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);

            // Enum: Κατάσταση κατηγορίας
            $table->string('status', 30)->default('active');

            // Flags πεδίου JSON
            $table->json('flags')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sample_types');
    }
};
