<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content',
        'attachment_type',
        'attachment_id',
        'parent_post_id',
    ];

    protected $with = ['user', 'attachment'];

    // 投稿者
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 添付されたコンテンツ (Trip, Photo, Suggestion)
    public function attachment(): MorphTo
    {
        return $this->morphTo();
    }

    // 元の投稿 (リツイート時)
    public function parentPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_post_id');
    }

    // この投稿への返信やリツイート
    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_post_id');
    }

    // リアクション
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }
}
