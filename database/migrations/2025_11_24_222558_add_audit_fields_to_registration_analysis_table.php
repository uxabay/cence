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
        Schema::table('registration_analysis', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('analysis_price');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            // Προαιρετικό: Αν θες foreign keys όπως στα υπόλοιπα
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('registration_analysis', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropColumn([
                'created_by',
                'updated_by',
            ]);
        });
    }

};
