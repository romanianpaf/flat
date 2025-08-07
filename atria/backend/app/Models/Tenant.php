<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'contact_email',
        'contact_phone',
        'address',
        'logo_url',
        'settings',
        'status', // 'active', 'inactive', 'suspended'
        'subscription_plan',
        'subscription_expires_at',
        'max_users',
        'max_automations',
        'features',
    ];

    protected $casts = [
        'settings' => 'array',
        'features' => 'array',
        'subscription_expires_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public function automations()
    {
        return $this->hasMany(Automation::class);
    }

    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function poolAccessLogs()
    {
        return $this->hasMany(PoolAccessLog::class);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isSubscriptionExpired()
    {
        return $this->subscription_expires_at && $this->subscription_expires_at->isPast();
    }

    public function canAddUser()
    {
        return $this->users()->count() < $this->max_users;
    }

    public function canAddAutomation()
    {
        return $this->automations()->count() < $this->max_automations;
    }

    public function hasFeature($feature)
    {
        return in_array($feature, $this->features ?? []);
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    public static function findByDomain($domain)
    {
        return self::where('domain', $domain)->first();
    }

    public static function findBySlug($slug)
    {
        return self::where('slug', $slug)->first();
    }
} 