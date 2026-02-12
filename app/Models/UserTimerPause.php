<?php
// app/Models/UserTimerPause.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserTimerLog;
use App\Models\User;

class UserTimerPause extends Model
{
    protected $fillable = [
        'user_timer_log_id',
        'user_id',
        'status',
        'pause_type',
        'remaining_seconds',
        'event_time'
    ];

    public function userTimerLog()
    {
        return $this->belongsTo(UserTimerLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
