<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'level',
        'metadata',
        'loggable_type',
        'loggable_id',
        'tenant_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function loggable()
    {
        return $this->morphTo();
    }

    public static function log($action, $description, $level = 'info', $metadata = null, $tenantId = null)
    {
        $user = auth()->user();
        
        return self::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'level' => $level,
            'metadata' => $metadata,
            'tenant_id' => $tenantId ?? ($user ? $user->tenant_id : null),
        ]);
    }

    public static function logInfo($action, $description, $metadata = null, $tenantId = null)
    {
        return self::log($action, $description, 'info', $metadata, $tenantId);
    }

    public static function logWarning($action, $description, $metadata = null, $tenantId = null)
    {
        return self::log($action, $description, 'warning', $metadata, $tenantId);
    }

    public static function logError($action, $description, $metadata = null, $tenantId = null)
    {
        return self::log($action, $description, 'error', $metadata, $tenantId);
    }

    public static function logSuccess($action, $description, $metadata = null, $tenantId = null)
    {
        return self::log($action, $description, 'success', $metadata, $tenantId);
    }

    public static function logForModel($model, $action, $description, $level = 'info', $metadata = null)
    {
        $user = auth()->user();
        
        return self::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'level' => $level,
            'metadata' => $metadata,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'tenant_id' => $user ? $user->tenant_id : null,
        ]);
    }
} 