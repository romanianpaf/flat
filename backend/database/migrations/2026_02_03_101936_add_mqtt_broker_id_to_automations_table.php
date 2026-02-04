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
            // Adaugă referință la broker-ul MQTT (opțional - pentru override poate fi null)
            $table->foreignId('mqtt_broker_id')
                ->nullable()
                ->after('tenant_id')
                ->constrained('tenant_mqtt_brokers')
                ->nullOnDelete();
            
            // Flag pentru a folosi configurare custom în loc de broker-ul selectat
            $table->boolean('use_custom_broker')->default(false)->after('mqtt_broker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            $table->dropForeign(['mqtt_broker_id']);
            $table->dropColumn(['mqtt_broker_id', 'use_custom_broker']);
        });
    }
};
