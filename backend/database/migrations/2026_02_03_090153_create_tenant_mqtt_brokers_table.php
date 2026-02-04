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
        Schema::create('tenant_mqtt_brokers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            
            // Identificator pentru broker (ex: "Principal", "Backup", "Pool Gate")
            $table->string('name')->default('Principal');
            
            // Configurare conexiune
            $table->string('host');
            $table->integer('port')->default(1883);
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Va fi criptat
            
            // Opțiuni
            $table->boolean('use_tls')->default(false);
            $table->integer('tls_port')->default(8883);
            $table->string('client_id_prefix')->nullable(); // Prefix pentru client ID
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Broker-ul implicit pentru tenant
            $table->timestamp('last_connected_at')->nullable();
            $table->string('last_connection_status')->nullable(); // ok, error, timeout
            $table->text('last_error_message')->nullable();
            
            $table->timestamps();
            
            // Un singur broker implicit per tenant
            $table->unique(['tenant_id', 'is_default'], 'unique_default_broker');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_mqtt_brokers');
    }
};
