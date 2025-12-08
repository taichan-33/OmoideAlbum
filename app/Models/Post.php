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

    /**
     * リアクション数と、特定のユーザーがリアクション済みかどうかの情報をロードするスコープ
     */
    public function scopeWithReactionDetails($query, $userId = null)
    {
        $query->withCount([
            'replies',
            'reactions as likes_count' => fn($q) => $q->where('type', 'like'),
            'reactions as funs_count' => fn($q) => $q->where('type', 'fun'),
            'reactions as want_to_go_count' => fn($q) => $q->where('type', 'want_to_go'),
            'reactions as on_hold_count' => fn($q) => $q->where('type', 'on_hold'),
            'reactions as interested_count' => fn($q) => $q->where('type', 'interested'),
        ]);

        if ($userId) {
            $query->withExists([
                'reactions as is_liked' => fn($q) => $q->where('user_id', $userId)->where('type', 'like'),
                'reactions as is_fun' => fn($q) => $q->where('user_id', $userId)->where('type', 'fun'),
                'reactions as is_want_to_go' => fn($q) => $q->where('user_id', $userId)->where('type', 'want_to_go'),
                'reactions as is_on_hold' => fn($q) => $q->where('user_id', $userId)->where('type', 'on_hold'),
                'reactions as is_interested' => fn($q) => $q->where('user_id', $userId)->where('type', 'interested'),
            ]);
        }

        return $query;
    }

    /**
     * タブに応じたフィルタリング
     */
    public function scopeFilterByTab($query, $tab, $userId)
    {
        return match ($tab) {
            'my_posts' => $query->where('user_id', $userId)->whereNull('parent_post_id'),
            'my_replies' => $query->where('user_id', $userId)->whereNotNull('parent_post_id'),
            default => $query->whereNull('parent_post_id'),  // timeline
        };
    }

    /**
     * リアクションをトグルする
     */
    public function toggleReaction(int $userId, string $type): void
    {
        $existingReaction = $this
            ->reactions()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();
        } else {
            $this->reactions()->create([
                'user_id' => $userId,
                'type' => $type,
            ]);
        }
    }
}
