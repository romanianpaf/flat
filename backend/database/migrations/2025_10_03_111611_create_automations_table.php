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
        Schema::create('automations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['switch', 'sensor', 'actuator', 'light', 'lock'])->default('switch');
            
            // MQTT Broker Configuration
            $table->string('mqtt_broker_host');
            $table->integer('mqtt_broker_port')->default(1883);
            $table->string('mqtt_broker_username')->nullable();
            $table->text('mqtt_broker_password')->nullable(); // Encrypted
            
            // MQTT Topic & Payload
            $table->string('mqtt_topic');
            $table->text('mqtt_payload_on')->nullable(); // JSON payload for ON/Open
            $table->text('mqtt_payload_off')->nullable(); // JSON payload for OFF/Close
            $table->tinyInteger('mqtt_qos')->default(0); // QoS: 0, 1, or 2
            
            // Status & Multi-tenancy
            $table->boolean('is_active')->default(true);
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('automations');
    }
};
