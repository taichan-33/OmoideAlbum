<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Trip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // ★ この $fillable を追加 ★
    protected $fillable = [
        'title',
        'description',
        'summary',  // AI要約用カラムを追加
        'prefecture',
        'start_date',
        'end_date',
        'nights',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prefecture' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    public function tags(): BelongsToMany
    {
        // 'tag_trip' 中間テーブルを経由して Tag モデルに関連付く
        return $this->belongsToMany(Tag::class, 'tag_trip');
    }

    /**
     * 検索フィルター (Scope)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['prefecture'] ?? null, function ($q, $pref) {
            $q->where('prefecture', 'LIKE', '%' . $pref . '%');
        })->when($filters['tag_id'] ?? null, function ($q, $tagId) {
            $q->whereHas('tags', fn($subQ) => $subQ->where('tag_id', $tagId));
        })->when($filters['date_from'] ?? null, function ($q, $date) {
            $q->where('start_date', '>=', $date);
        })->when($filters['date_to'] ?? null, function ($q, $date) {
            $q->where('start_date', '<=', $date);
        });

        return $query;
    }
}
