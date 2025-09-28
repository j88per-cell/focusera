<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsSession extends Model
{
    use HasFactory;

    protected $primaryKey = 'session_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_id',
        'user_id',
        'tenant_id',
        'first_seen',
        'last_seen',
        'device',
        'first_referrer',
        'geo_country',
        'geo_region',
        'geo_city',
    ];

    protected $casts = [
        'first_seen' => 'datetime',
        'last_seen' => 'datetime',
    ];
}
