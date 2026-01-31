<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrăm datele existente: împărțim name în first_name și last_name
        DB::statement("
            UPDATE users 
            SET first_name = SUBSTRING_INDEX(name, ' ', 1),
                last_name = CASE 
                    WHEN LOCATE(' ', name) > 0 
                    THEN SUBSTRING(name, LOCATE(' ', name) + 1)
                    ELSE ''
                END
            WHERE name IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
