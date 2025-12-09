<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class KeywordCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // キーワードが含まれる旅行回数
        $keyword = $value['keyword'] ?? '';
        $count = $value['count'] ?? 0;

        if (empty($keyword))
            return false;

        $matchedTrips = $user
            ->trips()
            ->where(function ($query) use ($keyword) {
                $query
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            })
            ->count();

        return $matchedTrips >= $count;
    }
}
