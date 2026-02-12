<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resume extends Model
{
    use HasFactory;

    protected $fillable = [
        'uploaded_by',
        'candidate_name',
        'email',
        'phone',
        'resume_file',
        'status',
    ];

    // Relationship with User (the uploader, usually Junior)
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
}
