<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->unsignedInteger('analyses_count')
                ->nullable()
                ->after('total_samples');

            $table->decimal('calculated_unit_price', 10, 2)
                ->nullable()
                ->after('analyses_count');

            $table->decimal('calculated_total', 10, 2)
                ->nullable()
                ->after('calculated_unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'analyses_count',
                'calculated_unit_price',
                'calculated_total',
            ]);
        });
    }
};
