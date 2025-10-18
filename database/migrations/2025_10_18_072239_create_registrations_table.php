<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_year_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_sample_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lab_customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lab_sample_category_id')->nullable()->constrained()->nullOnDelete();

            $table->string('protocol_number', 50);
            $table->date('protocol_date');

            $table->unsignedInteger('total_samples')->default(0);
            $table->unsignedInteger('invalid_samples')->default(0);
            $table->unsignedInteger('valid_samples')->default(0);

            $table->text('remarks')->nullable();

            $table->string('status', 20)->default('draft');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['protocol_number', 'protocol_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
