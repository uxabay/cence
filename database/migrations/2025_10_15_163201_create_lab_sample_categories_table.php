<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lab_sample_categories', function (Blueprint $table) {
            $table->id();

            // Βασικά στοιχεία
            $table->string('name', 255);
            $table->string('short_name', 100)->nullable();
            $table->string('code', 50)->nullable()->unique();
            $table->text('description')->nullable();

            // Συσχετίσεις
            $table->foreignId('lab_id')
                ->constrained('labs')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('sample_type_id')
                ->constrained('sample_types')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // Τιμολόγηση (default / τιμοκατάλογος)
            $table->decimal('default_price', 10, 2)->default(0);
            $table->string('currency_code', 10)->default('EUR'); // εναρμονισμένο με το Contracts σύστημα

            // Αναφορές μεθόδων
            $table->string('method_ref', 255)->nullable();
            $table->string('standard_ref', 255)->nullable();

            // Κατάσταση / Ταξινόμηση
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->nullable();

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

            // Indexes για reporting και ταχύτητα
            $table->index(['lab_id', 'sample_type_id']);
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_sample_categories');
    }
};
