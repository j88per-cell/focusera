<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'subject', 'message', 'status', 'read_at', 'responded_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'responded_at' => 'datetime',
    ];
}

