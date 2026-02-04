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
            $table->string('mqtt_broker_host')->nullable()->change();
            $table->integer('mqtt_broker_port')->nullable()->change();
            $table->string('mqtt_broker_username')->nullable()->change();
            $table->text('mqtt_broker_password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nu revenim - ar putea cauza probleme cu date existente
    }
};
