<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group', 'sub_group', 'key', 'value', 'description'
    ];

    public function scopeGroup($query, $group) {
        return $query->where('group', $group);
    }

    public function scopeSubGroup($query, $subGroup) {
        return $query->where('sub_group', $subGroup);
    }

}
