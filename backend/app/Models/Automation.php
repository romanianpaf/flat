<?php

namespace App\Models;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class Automation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'type',
        'mqtt_broker_host',
        'mqtt_broker_port',
        'mqtt_broker_username',
        'mqtt_broker_password',
        'mqtt_topic',
        'mqtt_payload_on',
        'mqtt_payload_off',
        'mqtt_qos',
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
        'mqtt_broker_port' => 'integer',
        'mqtt_qos' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'mqtt_broker_password',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
        
        // Setează automat tenant_id la creare
        static::creating(function ($automation) {
            if (auth()->check() && !$automation->tenant_id) {
                $automation->tenant_id = auth()->user()->tenant_id;
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
     * Set the MQTT broker password (encrypt).
     *
     * @param  string|null  $value
     * @return void
     */
    public function setMqttBrokerPasswordAttribute($value)
    {
        $this->attributes['mqtt_broker_password'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get the MQTT broker password (decrypt).
     *
     * @param  string|null  $value
     * @return string|null
     */
    public function getMqttBrokerPasswordAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
