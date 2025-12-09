<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class ManualTripCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 手動登録の旅行回数
        // 一旦「全ての旅行」で代用
        return $user->trips()->count() >= ($value['count'] ?? 0);
    }
}
