<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class AiSuggestionVisitedCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // AI提案を行って「行った」にした回数
        return $user
            ->suggestions()
            ->where('source', 'ai')  // AI提案
            ->where('is_visited', true)
            ->count() >= ($value['count'] ?? 0);
    }
}
