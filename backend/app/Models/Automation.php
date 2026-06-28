<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Trigger types for automation.
     */
    public const TRIGGER_MANUAL = 'manual';
    public const TRIGGER_SCHEDULED = 'scheduled';
    public const TRIGGER_MQTT_EVENT = 'mqtt_event';

    /**
     * Action types for automation.
     */
    public const ACTION_MQTT_PUBLISH = 'mqtt_publish';
    public const ACTION_WEBHOOK = 'webhook';
    public const ACTION_NOTIFICATION = 'notification';

    /**
     * Execution status types.
     */
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';
    public const STATUS_TIMEOUT = 'timeout';
    public const STATUS_PENDING = 'pending';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'trigger_type',
        'action_type',
        'schedule_cron',
        'mqtt_subscribe_topic',
        'mqtt_subscribe_payload_match',
        'mqtt_topic',
        'mqtt_payload_on',
        'mqtt_payload_off',
        'mqtt_qos',
        'mqtt_retain',
        'cooldown_ms',
        'is_active',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'mqtt_retain' => 'boolean',
        'mqtt_qos' => 'integer',
        'cooldown_ms' => 'integer',
        'last_run_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        // Setează automat tenant_id la creare
        static::creating(function ($automation) {
            if (auth()->check() && !$automation->tenant_id) {
                $automation->tenant_id = app(\App\Support\Tenancy\TenantResolver::class)->activeTenantId();
            }
        });
    }

    /**
     * Get the tenant that owns the automation.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the MQTT configuration from tenant.
     * Returns null if tenant has no MQTT configured.
     */
    public function getMqttConfig(): ?array
    {
        return $this->tenant?->getMqttConfig();
    }

    /**
     * Check if this automation can be executed (respects cooldown).
     */
    public function canExecute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->cooldown_ms <= 0) {
            return true;
        }

        if (!$this->last_run_at) {
            return true;
        }

        $cooldownSeconds = $this->cooldown_ms / 1000;
        return $this->last_run_at->addSeconds($cooldownSeconds)->isPast();
    }

    /**
     * Get the full MQTT topic with tenant prefix.
     */
    public function getFullMqttTopic(): ?string
    {
        $prefix = $this->tenant?->mqtt_topic_prefix ?? $this->tenant?->getSlug();
        
        if (!$prefix || !$this->mqtt_topic) {
            return $this->mqtt_topic;
        }

        // If topic already starts with prefix, return as-is
        if (str_starts_with($this->mqtt_topic, $prefix . '/')) {
            return $this->mqtt_topic;
        }

        return $this->mqtt_topic;
    }

    /**
     * Record an execution attempt.
     */
    public function recordExecution(string $status, ?string $error = null): void
    {
        $this->update([
            'last_run_at' => now(),
            'last_status' => $status,
            'last_error' => $error,
        ]);
    }

    /**
     * Get available trigger types.
     */
    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_MANUAL => 'Manual',
            self::TRIGGER_SCHEDULED => 'Programat',
            self::TRIGGER_MQTT_EVENT => 'Event MQTT',
        ];
    }

    /**
     * Get available action types.
     */
    public static function getActionTypes(): array
    {
        return [
            self::ACTION_MQTT_PUBLISH => 'Publish MQTT',
            self::ACTION_WEBHOOK => 'Webhook',
            self::ACTION_NOTIFICATION => 'Notificare',
        ];
    }
}
