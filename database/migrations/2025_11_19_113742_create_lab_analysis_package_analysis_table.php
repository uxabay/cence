<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_analysis_package_analysis', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('lab_analysis_package_id');
            $table->unsignedBigInteger('lab_analysis_id');

            // αποθηκεύουμε snapshot για ιστορικότητα
            $table->string('analysis_name');
            $table->decimal('analysis_price', 10, 2);

            $table->timestamps();

            $table->foreign('lab_analysis_package_id')
                ->references('id')
                ->on('lab_analysis_packages')
                ->cascadeOnDelete();

            $table->foreign('lab_analysis_id')
                ->references('id')
                ->on('lab_analyses')
                ->cascadeOnDelete();

            $table->unique(
                ['lab_analysis_package_id', 'lab_analysis_id'],
                'package_analysis_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_analysis_package_analysis');
    }
};
