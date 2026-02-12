<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'resume_id',
        'amount',
        'status',
        'transaction_id',
    ];

    // Customer who made the payment
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Resume linked to the payment
    public function resume()
    {
        return $this->belongsTo(Resume::class, 'resume_id');
    }

    // If training is linked (optional relation)
    public function training()
    {
        return $this->hasOne(Training::class, 'payment_id');
    }
}
