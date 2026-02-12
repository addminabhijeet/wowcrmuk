<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainer_id',
        'customer_id',
        'resume_id',
        'batch_name',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Relations
     */

    // The trainer who is assigned
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    // The customer who booked training
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // The resume associated with training
    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resume_id');
    }
}
