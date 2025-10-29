<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_customers', function (Blueprint $table) {
            $table->id();

            // Βασικά στοιχεία
            $table->string('name', 255);
            $table->foreignId('customer_category_id')
                ->constrained('customer_categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('contact_person', 255)->nullable();
            $table->string('phone', 50)->nullable();

            // Στοιχεία επικοινωνίας / τοποθεσίας
            $table->string('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();

            // Πρόσθετα αναγνωριστικά
            $table->string('tax_id', 20)->nullable();           // ΑΦΜ ή αντίστοιχο
            $table->string('organization_code', 50)->nullable(); // Εσωτερικός κωδικός ή μητρώο
            $table->string('email_primary', 255)->nullable();   // cache του primary email

            // Τεχνικά / metadata
            $table->string('encryption_key')->nullable();
            $table->timestamp('last_update_at')->nullable();

            // Κατάσταση
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
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

            // Indexes για απόδοση
            $table->index(['customer_category_id', 'status']);
            $table->index(['tax_id', 'organization_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_customers');
    }
};
