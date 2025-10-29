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

            // Βασικά στοιχεία πρωτοκόλλου
            $table->date('date')->nullable();
            $table->string('registration_number', 100)->nullable();

            // Συσχετίσεις
            $table->foreignId('lab_sample_category_id')
                ->nullable()
                ->constrained('lab_sample_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('contract_id')
                ->nullable()
                ->constrained('contracts')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('contract_sample_id')
                ->nullable()
                ->constrained('contract_samples')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('lab_customers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Πληροφορίες δειγμάτων
            $table->integer('num_samples_received')->default(0);
            $table->integer('not_valid_samples')->default(0);
            $table->integer('total_samples')->default(0); // υπολογιζόμενο (num - invalid)

            // Οικονομικά δεδομένα (snapshot)
            $table->decimal('unit_price_snapshot', 10, 2)->nullable();
            $table->string('currency_code', 10)->default('EUR');

            // Αναφορά έτους & σχόλια
            $table->smallInteger('year')->unsigned()->nullable();
            $table->text('comments')->nullable();

            // Κατάσταση (ενεργή/ανενεργή)
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
            $table->index(['registration_number']);
            $table->index(['contract_id', 'contract_sample_id']);
            $table->index(['customer_id', 'lab_sample_category_id']);
            $table->index(['year']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
