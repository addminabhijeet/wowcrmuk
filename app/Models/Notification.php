<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    public $timestamps = true; // IMPORTANT ✔

    protected $fillable = [
        'type',
        'candidate_id',
        'notifiable_role',
        'notifiable_id',
        'data',
        'read_at',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'notifiable_id');
    }

    public function candidate()
    {
        return $this->belongsTo(GoogleSheetData::class, 'candidate_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }
}
