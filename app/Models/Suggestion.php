<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Suggestion extends Model
{
    use HasFactory;

    /**
     * マスアサインメント可能な属性
     */
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'recommendation_score',  // ★ これを追加
        'itinerary_data',
        'accommodation',  // ★ これを追加
        'local_food',
        'prefecture_code',
        'is_visited',
        'source',
    ];

    /**
     * ★ (追加) カラムの型を自動変換する
     *
     * @var array
     */
    protected $casts = [
        // itinerary_data カラムを自動的に JSON (配列/オブジェクト) として扱う
        // itinerary_data カラムを自動的に JSON (配列/オブジェクト) として扱う
        'itinerary_data' => 'array',
        'content' => 'array',  // ★ contentもJSONとして扱う
    ];

    /**
     * この提案を所有するユーザーを取得
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearchKeyword(Builder $query, ?string $keyword): Builder
    {
        if (blank($keyword)) {
            return $query;
        }

        $searchKeyword = '%'.$keyword.'%';

        return $query->where(function (Builder $builder) use ($searchKeyword) {
            $builder
                ->where('title', 'LIKE', $searchKeyword)
                ->orWhere('content', 'LIKE', $searchKeyword);
        });
    }

    public function scopeFromSource(Builder $query, ?string $source): Builder
    {
        if (blank($source) || $source === 'all') {
            return $query;
        }

        return $query->where('source', $source);
    }

    public function scopeSortByOption(Builder $query, ?string $sort): Builder
    {
        return match ($sort) {
            'score_desc' => $query->orderBy('recommendation_score', 'desc'),
            'score_asc' => $query->orderBy('recommendation_score', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'attachment');
    }
}
