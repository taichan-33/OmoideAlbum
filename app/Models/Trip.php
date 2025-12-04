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
        // 'user_id', // 認証機能と紐づける際に後で追加します
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
}
