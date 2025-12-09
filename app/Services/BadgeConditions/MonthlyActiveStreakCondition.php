<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class MonthlyActiveStreakCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 過去Nヶ月連続でアクティブ（旅行or写真）
        // 簡易実装: 過去Nヶ月の各月に旅行があるかチェック
        $months = $value['months'] ?? 12;
        for ($i = 0; $i < $months; $i++) {
            $date = now()->subMonths($i);
            $exists = $user
                ->trips()
                ->whereMonth('start_date', $date->month)
                ->whereYear('start_date', $date->year)
                ->exists();
            if (!$exists)
                return false;
        }
        return true;
    }
}
