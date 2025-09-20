<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

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

    public function isSecret(): bool
    {
        $g = strtolower((string) $this->group);
        $sg = strtolower((string) ($this->sub_group ?? ''));
        $k = strtolower((string) $this->key);
        if ($g === 'sales' && str_contains($sg, 'pwinty')) {
            return str_contains($k, 'key') || str_contains($k, 'secret');
        }
        return false;
    }

    public function setValueAttribute($value): void
    {
        $raw = is_null($value) ? null : (string) $value;
        if ($this->isSecret() && !is_null($raw)) {
            $this->attributes['value'] = Crypt::encryptString($raw);
        } else {
            $this->attributes['value'] = $raw;
        }
    }

    public function getValueAttribute($value): ?string
    {
        if (is_null($value)) return null;
        if ($this->isSecret()) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable $e) {
                return null;
            }
        }
        return (string) $value;
    }
}
