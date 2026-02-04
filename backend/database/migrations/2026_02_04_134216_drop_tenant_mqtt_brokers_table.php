<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Șterge tabelul tenant_mqtt_brokers - nu mai e necesar.
     * Noua arhitectură folosește configurația MQTT direct pe tenant.
     */
    public function up(): void
    {
        Schema::dropIfExists('tenant_mqtt_brokers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tenant_mqtt_brokers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(1883);
            $table->string('username')->nullable();
            $table->text('password')->nullable();
            $table->boolean('use_tls')->default(false);
            $table->integer('tls_port')->default(8883);
            $table->string('client_id_prefix')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamp('last_connected_at')->nullable();
            $table->enum('last_connection_status', ['ok', 'error', 'timeout', 'auth_error', 'connection_refused'])->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'is_default']);
        });
    }
};
