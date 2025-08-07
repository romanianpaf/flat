<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoolAccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'user_id',
        'action', // 'unlock', 'lock', 'access_granted', 'access_denied'
        'mqtt_message',
        'esp_response',
        'ip_address',
        'user_agent',
        'status', // 'success', 'failed', 'pending'
        'metadata',
        'tenant_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function logAccess($automationId, $userId, $action, $mqttMessage = null, $espResponse = null, $status = 'success', $metadata = [], $tenantId = null)
    {
        $user = auth()->user();
        
        return self::create([
            'automation_id' => $automationId,
            'user_id' => $userId,
            'action' => $action,
            'mqtt_message' => $mqttMessage,
            'esp_response' => $espResponse,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => $status,
            'metadata' => $metadata,
            'tenant_id' => $tenantId ?? ($user ? $user->tenant_id : null),
        ]);
    }

    public function getActionDescription()
    {
        $descriptions = [
            'unlock' => 'Deblocare zăvor',
            'lock' => 'Blocare zăvor',
            'access_granted' => 'Acces acordat',
            'access_denied' => 'Acces refuzat',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }

    public function getStatusColor()
    {
        return [
            'success' => 'green',
            'failed' => 'red',
            'pending' => 'yellow',
        ][$this->status] ?? 'gray';
    }
} 