<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class ShortTripCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // N泊以下の旅行回数
        $nights = $value['nights'] ?? 1;
        $count = $value['count'] ?? 0;
        $shortTrips = $user->trips()->where('nights', '<=', $nights)->count();
        return $shortTrips >= $count;
    }
}
