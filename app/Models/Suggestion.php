<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

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
}
