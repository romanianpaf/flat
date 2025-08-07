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
        // Add tenant_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('set null');
            $table->enum('role', ['sysadmin', 'admin', 'tenantadmin', 'cex', 'tehnic', 'user'])->default('user')->after('password');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('role');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->json('settings')->nullable()->after('last_login_at');
            
            $table->index(['tenant_id', 'role']);
            $table->index(['tenant_id', 'status']);
        });

        // Add tenant_id to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->json('permissions')->nullable()->after('is_active');
            
            $table->index(['tenant_id', 'is_active']);
        });

        // Add tenant_id to automations table
        Schema::table('automations', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);
        });

        // Add tenant_id to logs table
        Schema::table('logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'level']);
        });

        // Add tenant_id to pool_access_logs table
        Schema::table('pool_access_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            
            $table->index(['tenant_id', 'created_at']);
            $table->index(['tenant_id', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove tenant_id from pool_access_logs table
        Schema::table('pool_access_logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['tenant_id', 'action']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Remove tenant_id from logs table
        Schema::table('logs', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['tenant_id', 'level']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Remove tenant_id from automations table
        Schema::table('automations', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'type']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Remove tenant_id from roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_active']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'permissions']);
        });

        // Remove tenant_id from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'role']);
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role', 'status', 'last_login_at', 'settings']);
        });
    }
};
