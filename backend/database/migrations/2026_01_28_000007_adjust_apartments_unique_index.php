<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            // index-ul inițial includea floor; în MySQL NULL permite duplicate.
            // Stabilim unicitatea pe (tenant_id, number, staircase).
            $table->dropUnique('apartments_tenant_number_unique');
            $table->unique(['tenant_id', 'number', 'staircase'], 'apartments_tenant_number_staircase_unique');
        });
    }

    public function down(): void
    {
        Schema::table('apartments', function (Blueprint $table) {
            $table->dropUnique('apartments_tenant_number_staircase_unique');
            $table->unique(['tenant_id', 'number', 'staircase', 'floor'], 'apartments_tenant_number_unique');
        });
    }
};

