<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'published_at', 'author_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public static function booted(): void
    {
        static::saving(function (NewsPost $post) {
            if (!$post->slug) {
                $base = Str::slug($post->title ?: Str::random(8));
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                    $slug = $base . '-' . (++$i);
                }
                $post->slug = $slug;
            }
        });
    }
}
