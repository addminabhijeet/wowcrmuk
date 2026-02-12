<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserTimerLog extends Model
{
    protected $table = 'user_timer_logs';

    protected $fillable = [
        'user_id',
        'login_id',
        'start_time',
        'remaining_seconds',
        'status',
        'pause_type',
        'last_decrement',
        'button_status',
        'notice_status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'last_decrement' => 'datetime',
        'button_status' => 'boolean',
        'notice_status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
