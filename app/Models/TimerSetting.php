<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_day_seconds',
        'daily_base_time',
    ];
}
