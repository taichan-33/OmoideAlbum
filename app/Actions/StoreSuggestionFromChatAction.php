<?php

namespace App\Actions;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StoreSuggestionFromChatAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function __invoke(User $user, array $attributes): Suggestion
    {
        return DB::transaction(function () use ($user, $attributes) {
            $suggestion = $user->suggestions()->create([
                'title' => $attributes['title'],
                'recommendation_score' => 5,
                'content' => $attributes['content'] ?? '',
                'accommodation' => $attributes['accommodation'] ?? null,
                'local_food' => $attributes['local_food'] ?? null,
                'itinerary_data' => $attributes['itinerary'],
                'prefecture_code' => $attributes['prefecture_code'],
                'source' => 'chat',
            ]);

            DB::table('pinned_locations')->insertOrIgnore([
                'user_id' => $user->id,
                'prefecture_code' => $attributes['prefecture_code'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $suggestion;
        });
    }
}
