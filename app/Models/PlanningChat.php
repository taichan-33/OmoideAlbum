<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlanningChat extends Model
{
    protected $fillable = [
        'prefecture_code',
        'user_id',
        'message',
        'is_ai',
    ];

    protected $casts = [
        'is_ai' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForPrefecture(Builder $query, string $prefectureCode): Builder
    {
        return $query->where('prefecture_code', $prefectureCode);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPlannerMessage(?int $currentUserId): array
    {
        return [
            'id' => $this->id,
            'role' => $this->is_ai ? 'assistant' : 'user',
            'content' => $this->message,
            'user_id' => $this->user_id,
            'user_name' => $this->is_ai ? 'AI Planner' : ($this->user?->name ?? 'Unknown'),
            'is_me' => $this->user_id === $currentUserId,
        ];
    }
}
