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
        // Modify existing users table from Laravel's default
        Schema::table('users', function (Blueprint $table) {
            // Add new columns
            $table->string('username', 50)->unique()->after('id');
            $table->string('employee_id', 30)->nullable()->unique()->after('username');
            $table->foreignId('role_id')->nullable()->after('password')->constrained('roles')->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->after('role_id')->constrained('units')->onDelete('set null');
            $table->foreignId('position_id')->nullable()->after('unit_id')->constrained('positions')->onDelete('set null');
            $table->string('phone', 20)->nullable()->after('position_id');
            $table->string('avatar')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->boolean('must_change_password')->default(false)->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('must_change_password');
            $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_ip');
            $table->softDeletes();

            // Make email nullable (username is primary identifier)
            $table->string('email')->nullable()->change();

            // Add indexes
            $table->index('is_active');
            $table->index('role_id');
            $table->index('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['is_active']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['unit_id']);
            $table->dropForeign(['role_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn([
                'username',
                'employee_id',
                'role_id',
                'unit_id',
                'position_id',
                'phone',
                'avatar',
                'is_active',
                'must_change_password',
                'last_login_at',
                'last_login_ip',
                'password_changed_at',
            ]);
        });
    }
};
