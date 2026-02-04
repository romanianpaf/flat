<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'registration_instructions',
        'address',
        'fiscal_code',
        'description',
        'contact_data',
        // MQTT mTLS configuration
        'mqtt_host',
        'mqtt_port',
        'mqtt_ca_path',
        'mqtt_client_cert_path',
        'mqtt_client_key_path',
        'mqtt_topic_prefix',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contact_data' => 'array',
        'mqtt_port' => 'integer',
    ];

    /**
     * Get the roles for the tenant.
     */
    public function roles()
    {
        return $this->hasMany(\Spatie\Permission\Models\Role::class);
    }

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the automations for the tenant.
     */
    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    /**
     * Get the polls for the tenant.
     */
    public function polls()
    {
        return $this->hasMany(Poll::class);
    }

    /**
     * Get the registration requests for the tenant.
     */
    public function registrationRequests()
    {
        return $this->hasMany(RegistrationRequest::class);
    }

    /**
     * Get the apartments for the tenant.
     */
    public function apartments()
    {
        return $this->hasMany(Apartment::class);
    }

    /**
     * Get the staircases for the tenant.
     */
    public function staircases()
    {
        return $this->hasMany(Staircase::class);
    }

    /**
     * Check if MQTT is configured for this tenant.
     */
    public function hasMqttConfigured(): bool
    {
        return !empty($this->mqtt_host) 
            && !empty($this->mqtt_ca_path)
            && !empty($this->mqtt_client_cert_path)
            && !empty($this->mqtt_client_key_path);
    }

    /**
     * Get the MQTT configuration for this tenant.
     * Returns null if MQTT is not configured.
     */
    public function getMqttConfig(): ?array
    {
        if (!$this->hasMqttConfigured()) {
            return null;
        }

        return [
            'host' => $this->mqtt_host,
            'port' => $this->mqtt_port ?? 8883,
            'ca_path' => $this->mqtt_ca_path,
            'cert_path' => $this->mqtt_client_cert_path,
            'key_path' => $this->mqtt_client_key_path,
            'topic_prefix' => $this->mqtt_topic_prefix ?? $this->getSlug(),
        ];
    }

    /**
     * Get the slug for this tenant (used as topic prefix if not set).
     */
    public function getSlug(): string
    {
        return \Illuminate\Support\Str::slug($this->name);
    }

    /**
     * Build a full MQTT topic for this tenant.
     * 
     * @param string $category One of: cmd, state, evt
     * @param string $device Device identifier
     * @param string $action Action or property name
     */
    public function buildMqttTopic(string $category, string $device, string $action): string
    {
        $prefix = $this->mqtt_topic_prefix ?? $this->getSlug();
        return "{$prefix}/{$category}/{$device}/{$action}";
    }

    /**
     * Get the CN (Common Name) from the client certificate.
     * Useful for debug/audit.
     */
    public function getMqttClientCN(): ?string
    {
        if (empty($this->mqtt_client_cert_path) || !file_exists($this->mqtt_client_cert_path)) {
            return null;
        }

        try {
            $certContent = file_get_contents($this->mqtt_client_cert_path);
            $certData = openssl_x509_parse($certContent);
            
            return $certData['subject']['CN'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get recommended QoS for a topic category.
     * 
     * Convention:
     * - cmd/   → QoS 1 (at least once) - commands must be delivered
     * - state/ → QoS 1 (at least once) - state updates are important
     * - evt/   → QoS 0 (at most once) - events are transient
     * - telemetry/ → QoS 0 (at most once) - high frequency, loss acceptable
     */
    public static function getRecommendedQos(string $category): int
    {
        return match ($category) {
            'cmd', 'state' => 1,
            'evt', 'event', 'telemetry' => 0,
            default => 1,
        };
    }
}
