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
        Schema::table('automations', function (Blueprint $table) {
            // Drop the existing enum constraint
            $table->dropColumn('type');
        });
        
        Schema::table('automations', function (Blueprint $table) {
            // Add the new enum with extended types
            $table->enum('type', [
                'pool_access', 
                'proxy', 
                'barrier_access',
                'building_access', 
                'visitor_parking',
                'other'
            ])->default('other')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            // Drop the extended enum
            $table->dropColumn('type');
        });
        
        Schema::table('automations', function (Blueprint $table) {
            // Restore the original enum
            $table->enum('type', ['pool_access', 'proxy', 'other'])->default('other')->after('name');
        });
    }
};
