<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    /**
     * マスアサインメント可能な属性
     */
    protected $fillable = [
        'trip_id',
        'path',
        'caption',
    ];

    /**
     * この写真が属する旅行を取得
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function comments()
    {
        return $this->hasMany(PhotoComment::class);
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'attachment');
    }

    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return \Illuminate\Support\Facades\Storage::url($this->path);
    }
}
