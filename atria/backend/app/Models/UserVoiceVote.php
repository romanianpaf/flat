<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoiceVote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'item_id',
        'user_id',
    ];

    public function item()
    {
        return $this->belongsTo(UserVoiceItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
