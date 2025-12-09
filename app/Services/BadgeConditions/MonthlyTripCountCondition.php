<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class MonthlyTripCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 月にN回以上旅行したか
        // 直近の月で判定（簡易実装）
        $currentMonthTrips = $user
            ->trips()
            ->whereMonth('start_date', now()->month)
            ->whereYear('start_date', now()->year)
            ->count();

        return ($currentMonthTrips ?? 0) >= ($value['count'] ?? 0);
    }
}
