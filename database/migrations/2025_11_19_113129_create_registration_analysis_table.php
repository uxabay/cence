<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registration_analysis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id');
            $table->unsignedBigInteger('lab_analysis_id');
            $table->string('analysis_name');
            $table->decimal('analysis_price', 10, 2);
            $table->timestamps();

            $table->foreign('registration_id')
                ->references('id')
                ->on('registrations')
                ->cascadeOnDelete();

            $table->foreign('lab_analysis_id')
                ->references('id')
                ->on('lab_analyses')
                ->cascadeOnDelete();

            $table->unique(['registration_id', 'lab_analysis_id'], 'registration_analysis_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registration_analysis');
    }
};
