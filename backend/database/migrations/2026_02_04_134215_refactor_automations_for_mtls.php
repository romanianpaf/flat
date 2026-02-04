<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Refactorizează tabelul automations pentru noua arhitectură mTLS.
     * - Elimină câmpurile de broker custom (configurația vine de la tenant)
     * - Adaugă câmpuri pentru trigger/action types și tracking execuție
     */
    public function up(): void
    {
        // Adaugă câmpuri noi doar dacă nu există deja
        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'trigger_type')) {
                $table->enum('trigger_type', ['manual', 'scheduled', 'mqtt_event'])
                    ->default('manual')
                    ->after('type')
                    ->comment('Tipul de declanșare: manual, programat sau event MQTT');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'action_type')) {
                $table->enum('action_type', ['mqtt_publish', 'webhook', 'notification'])
                    ->default('mqtt_publish')
                    ->after('trigger_type')
                    ->comment('Tipul de acțiune: publish MQTT, webhook sau notificare');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'mqtt_retain')) {
                $table->boolean('mqtt_retain')->default(false)->after('mqtt_qos')
                    ->comment('Dacă mesajul MQTT trebuie reținut');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'cooldown_ms')) {
                $table->integer('cooldown_ms')->default(0)->after('mqtt_retain')
                    ->comment('Cooldown între execuții (milisecunde)');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'last_run_at')) {
                $table->timestamp('last_run_at')->nullable()->after('cooldown_ms')
                    ->comment('Timestamp ultima execuție');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'last_status')) {
                $table->enum('last_status', ['success', 'error', 'timeout', 'pending'])
                    ->nullable()
                    ->after('last_run_at')
                    ->comment('Statusul ultimei execuții');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'last_error')) {
                $table->text('last_error')->nullable()->after('last_status')
                    ->comment('Mesaj de eroare ultima execuție');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'schedule_cron')) {
                $table->string('schedule_cron')->nullable()->after('action_type')
                    ->comment('Expresie cron pentru trigger programat');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'mqtt_subscribe_topic')) {
                $table->string('mqtt_subscribe_topic')->nullable()->after('schedule_cron')
                    ->comment('Topic MQTT pentru trigger event-based');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (!Schema::hasColumn('automations', 'mqtt_subscribe_payload_match')) {
                $table->string('mqtt_subscribe_payload_match')->nullable()->after('mqtt_subscribe_topic')
                    ->comment('Pattern pentru matching payload (regex sau exact)');
            }
        });

        // Elimină câmpurile vechi de broker
        Schema::table('automations', function (Blueprint $table) {
            // Drop foreign key first if exists
            if (Schema::hasColumn('automations', 'mqtt_broker_id')) {
                // Drop foreign key by convention name
                try {
                    $table->dropForeign('automations_mqtt_broker_id_foreign');
                } catch (\Exception $e) {
                    // Foreign key might not exist
                }
                
                $table->dropColumn('mqtt_broker_id');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'use_custom_broker')) {
                $table->dropColumn('use_custom_broker');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'mqtt_broker_host')) {
                $table->dropColumn('mqtt_broker_host');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'mqtt_broker_port')) {
                $table->dropColumn('mqtt_broker_port');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'mqtt_broker_username')) {
                $table->dropColumn('mqtt_broker_username');
            }
        });

        Schema::table('automations', function (Blueprint $table) {
            if (Schema::hasColumn('automations', 'mqtt_broker_password')) {
                $table->dropColumn('mqtt_broker_password');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('automations', function (Blueprint $table) {
            // Restaurează câmpurile vechi
            $table->foreignId('mqtt_broker_id')->nullable()->constrained('tenant_mqtt_brokers')->nullOnDelete();
            $table->boolean('use_custom_broker')->default(false);
            $table->string('mqtt_broker_host')->nullable();
            $table->integer('mqtt_broker_port')->nullable();
            $table->string('mqtt_broker_username')->nullable();
            $table->text('mqtt_broker_password')->nullable();
        });

        Schema::table('automations', function (Blueprint $table) {
            // Elimină câmpurile noi
            $table->dropColumn([
                'trigger_type',
                'action_type',
                'schedule_cron',
                'mqtt_subscribe_topic',
                'mqtt_subscribe_payload_match',
                'mqtt_retain',
                'cooldown_ms',
                'last_run_at',
                'last_status',
                'last_error',
            ]);
        });
    }
};
