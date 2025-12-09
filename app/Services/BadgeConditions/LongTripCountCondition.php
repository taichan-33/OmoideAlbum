<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class LongTripCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // N泊以上の旅行回数
        $nights = $value['nights'] ?? 3;
        $count = $value['count'] ?? 0;
        $longTrips = $user->trips()->where('nights', '>=', $nights)->count();
        return $longTrips >= $count;
    }
}
