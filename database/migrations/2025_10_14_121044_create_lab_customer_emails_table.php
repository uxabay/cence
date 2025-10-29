<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_customer_emails', function (Blueprint $table) {
            $table->id();

            // Συσχέτιση με πελάτη
            $table->foreignId('lab_customer_id')
                ->constrained('lab_customers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Πληροφορίες email
            $table->string('email', 255);
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();

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
            $table->index(['lab_customer_id', 'is_primary']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_customer_emails');
    }
};
