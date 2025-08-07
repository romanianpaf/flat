<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'pool_access', 'proxy', 'other'
        'mqtt_broker',
        'mqtt_port',
        'mqtt_username',
        'mqtt_password',
        'mqtt_topic_control',
        'mqtt_topic_status',
        'esp_device_id',
        'lock_relay_pin',
        'status',
        'config',
        'description',
        'tenant_id',
    ];

    protected $casts = [
        'config' => 'array',
        'status' => 'boolean',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs()
    {
        return $this->morphMany(Log::class, 'loggable');
    }

    public function accessLogs()
    {
        return $this->hasMany(PoolAccessLog::class);
    }

    public static function getPoolAccess($tenantId = null)
    {
        $query = self::where('type', 'pool_access');
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        return $query->first();
    }

    public static function getProxyConfig($tenantId = null)
    {
        $query = self::where('type', 'proxy');
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }
        return $query->first();
    }

    public function getMqttConfig()
    {
        return [
            'broker' => $this->mqtt_broker,
            'port' => $this->mqtt_port,
            'username' => $this->mqtt_username,
            'password' => $this->mqtt_password,
            'topic_control' => $this->mqtt_topic_control,
            'topic_status' => $this->mqtt_topic_status,
        ];
    }

    public function getEspConfig()
    {
        return [
            'device_id' => $this->esp_device_id,
            'lock_relay_pin' => $this->lock_relay_pin,
        ];
    }
} 