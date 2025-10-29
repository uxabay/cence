<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_sample_categories', function (Blueprint $table) {
            $table->id();

            // Βασικά πεδία
            $table->string('code', 50)->unique();           // μοναδικός κωδικός κατηγορίας
            $table->string('name', 255);                    // τίτλος κατηγορίας
            $table->text('description')->nullable();        // περιγραφή

            // Κατάσταση (π.χ. ενεργή / ανενεργή)
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

            // Indexes για ταχύτητα αναζήτησης
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_sample_categories');
    }
};
