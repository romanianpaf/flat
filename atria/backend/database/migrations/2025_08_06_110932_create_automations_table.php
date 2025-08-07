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
            $table->enum('type', ['pool_access', 'proxy', 'other'])->default('other');
            $table->string('mqtt_broker')->nullable();
            $table->integer('mqtt_port')->default(1883);
            $table->string('mqtt_username')->nullable();
            $table->string('mqtt_password')->nullable();
            $table->string('mqtt_topic_control')->nullable();
            $table->string('mqtt_topic_status')->nullable();
            $table->string('esp_device_id')->nullable();
            $table->integer('lock_relay_pin')->nullable();
            $table->boolean('status')->default(true);
            $table->json('config')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index('mqtt_broker');
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
