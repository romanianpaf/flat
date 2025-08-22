<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Asigură-te că enum-ul include 'tenantadmin' și 'locatar'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sysadmin', 'admin', 'tenantadmin', 'cex', 'tehnic', 'locatar', 'user') DEFAULT 'user'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revine la enum fără 'tenantadmin' și 'locatar'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('sysadmin', 'admin', 'cex', 'tehnic', 'user') DEFAULT 'user'");
    }
};
