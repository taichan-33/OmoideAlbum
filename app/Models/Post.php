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
        'attachment_id',
        'parent_post_id',
        'root_post_id',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            if ($post->parent_post_id) {
                $parent = Post::find($post->parent_post_id);
                $post->root_post_id = $parent->root_post_id ?? $parent->id;
            } else {
                // 親がない場合は自分自身がルートになるが、IDはまだないのでnullのままにしておくか、
                // createdで更新するか。
                // ここではnullableなのでnullにしておき、createdで更新する手もあるが、
                // IDが決まるのはsave後。
                // ルート投稿の場合はroot_post_id = idとしたいなら、createdでやる必要がある。
                // しかし、root_post_idが自分自身を指す必要があるか？
                // 「スレッドのルート」という意味なら、ルート投稿は自分自身を指すのが自然。
            }
        });

        static::created(function ($post) {
            // ルート投稿の場合、自分自身をroot_post_idに設定
            if (is_null($post->parent_post_id) && is_null($post->root_post_id)) {
                $post->root_post_id = $post->id;
                $post->saveQuietly();
            }

            // 子投稿でroot_post_idが未設定の場合のフォールバック
            if ($post->parent_post_id && is_null($post->root_post_id)) {
                $parent = Post::find($post->parent_post_id);
                if ($parent) {
                    $post->root_post_id = $parent->root_post_id ?? $parent->id;
                    $post->saveQuietly();
                }
            }

            // メンション検出 (@ユーザー名)
            // 空白、改行、全角スペースなどで区切られた @ から始まる文字列を抽出
            if (preg_match_all("/@([^\s\u{3000}]+)/u", $post->content, $matches)) {
                $mentionedNames = array_unique($matches[1]);

                if (!empty($mentionedNames)) {
                    $users = User::whereIn('name', $mentionedNames)->get();

                    foreach ($users as $user) {
                        // 自分自身へのメンションは通知しない
                        if ($user->id !== $post->user_id) {
                            $user->notify(new \App\Notifications\PostInteracted(
                                $post->user,
                                $post,
                                $post,  // targetPost is the post itself for mentions
                                'mention'
                            ));

                            // Botへのメンションなら返信ジョブをディスパッチ
                            // Botの判定: emailがconfig('services.bot.email')と一致するか、または名前が「クイックン」か
                            // ここでは名前判定も入れておくが、基本はEmail推奨
                            if ($user->email === config('services.bot.email') || $user->name === 'クイックン') {
                                \App\Jobs\GenerateBotReply::dispatch($post, $user);
                            }
                        }
                    }
                }
            }
        });
    }

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
            'reactions as likes_count' => fn($q) => $q->where('type', \App\Enums\ReactionType::LIKE),
            'reactions as funs_count' => fn($q) => $q->where('type', \App\Enums\ReactionType::FUN),
            'reactions as want_to_go_count' => fn($q) => $q->where('type', \App\Enums\ReactionType::WANT_TO_GO),
            'reactions as on_hold_count' => fn($q) => $q->where('type', \App\Enums\ReactionType::ON_HOLD),
            'reactions as interested_count' => fn($q) => $q->where('type', \App\Enums\ReactionType::INTERESTED),
        ]);

        if ($userId) {
            $query->withExists([
                'reactions as is_liked' => fn($q) => $q->where('user_id', $userId)->where('type', \App\Enums\ReactionType::LIKE),
                'reactions as is_fun' => fn($q) => $q->where('user_id', $userId)->where('type', \App\Enums\ReactionType::FUN),
                'reactions as is_want_to_go' => fn($q) => $q->where('user_id', $userId)->where('type', \App\Enums\ReactionType::WANT_TO_GO),
                'reactions as is_on_hold' => fn($q) => $q->where('user_id', $userId)->where('type', \App\Enums\ReactionType::ON_HOLD),
                'reactions as is_interested' => fn($q) => $q->where('user_id', $userId)->where('type', \App\Enums\ReactionType::INTERESTED),
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
