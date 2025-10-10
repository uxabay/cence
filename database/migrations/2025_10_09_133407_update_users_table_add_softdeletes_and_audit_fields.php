<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ✅ SoftDeletes
            $table->softDeletes();

            // ✅ Activity / Audit fields
            $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
            $table->timestamp('last_activity_at')->nullable()->after('last_login_at');

            // ✅ Account control
            $table->boolean('force_password_reset')->default(false)->after('last_activity_at');
            $table->enum('status', ['active', 'suspended', 'archived'])
                  ->default('active')
                  ->after('force_password_reset');

            // ✅ Tracking of creator/updater (optional, useful for audits)
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();

            $table->dropColumn([
                'last_login_at',
                'last_activity_at',
                'force_password_reset',
                'status',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
