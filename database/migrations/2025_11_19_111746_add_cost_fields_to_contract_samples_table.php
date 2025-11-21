<?php

use App\Enums\CostCalculationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_samples', function (Blueprint $table) {
            $table->string('cost_calculation_type', 20)
                ->default(CostCalculationTypeEnum::FIX->value)
                ->after('price');

            $table->unsignedInteger('max_analyses')
                ->default(0)
                ->after('cost_calculation_type');
        });
    }

    public function down(): void
    {
        Schema::table('contract_samples', function (Blueprint $table) {
            $table->dropColumn(['cost_calculation_type', 'max_analyses']);
        });
    }
};
