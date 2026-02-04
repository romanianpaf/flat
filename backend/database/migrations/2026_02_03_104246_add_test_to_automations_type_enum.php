<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adaugă 'test' la enum-ul type
        DB::statement("ALTER TABLE automations MODIFY COLUMN type ENUM('switch','sensor','actuator','light','lock','test') NOT NULL DEFAULT 'switch'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert la enum-ul original
        DB::statement("ALTER TABLE automations MODIFY COLUMN type ENUM('switch','sensor','actuator','light','lock') NOT NULL DEFAULT 'switch'");
    }
};
