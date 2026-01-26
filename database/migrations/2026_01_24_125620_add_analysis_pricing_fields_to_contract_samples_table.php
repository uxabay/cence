<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_samples', function (Blueprint $table) {
            $table->decimal('analysis_unit_price', 10, 2)
                ->nullable()
                ->after('price');
        });
    }

    public function down(): void
    {
        Schema::table('contract_samples', function (Blueprint $table) {
            $table->dropColumn('analysis_unit_price');
        });
    }
};
