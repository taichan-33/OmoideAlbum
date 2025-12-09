<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class TotalPhotoCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 総写真枚数
        // User -> photos relation (hasManyThrough)
        return $user->photos()->count() >= ($value['count'] ?? 0);
    }
}
