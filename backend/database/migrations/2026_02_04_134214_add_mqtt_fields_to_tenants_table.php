<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adaugă câmpurile necesare pentru conectarea mTLS la broker-ul MQTT al tenant-ului.
     * Fiecare tenant are propriul miniPC cu Mosquitto, conectat prin WireGuard.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Conexiune MQTT
            $table->string('mqtt_host')->nullable()->after('contact_data')
                ->comment('IP WireGuard al miniPC (ex: 10.10.0.10)');
            $table->integer('mqtt_port')->default(8883)->after('mqtt_host')
                ->comment('Port TLS pentru MQTT');
            
            // Certificate mTLS - doar path-uri, cheile stau pe disk
            $table->string('mqtt_ca_path')->nullable()->after('mqtt_port')
                ->comment('Path către CA chain (ex: /etc/mqtt/tenants/atria/ca-chain.crt)');
            $table->string('mqtt_client_cert_path')->nullable()->after('mqtt_ca_path')
                ->comment('Path către certificatul client VPS');
            $table->string('mqtt_client_key_path')->nullable()->after('mqtt_client_cert_path')
                ->comment('Path către cheia privată client VPS');
            
            // Prefix topicuri
            $table->string('mqtt_topic_prefix')->nullable()->after('mqtt_client_key_path')
                ->comment('Prefix pentru topicuri MQTT (ex: atria pentru atria/cmd/...)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'mqtt_host',
                'mqtt_port',
                'mqtt_ca_path',
                'mqtt_client_cert_path',
                'mqtt_client_key_path',
                'mqtt_topic_prefix',
            ]);
        });
    }
};
