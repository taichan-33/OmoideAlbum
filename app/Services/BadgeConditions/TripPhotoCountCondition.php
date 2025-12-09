<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class TripPhotoCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 1回の旅行でN枚以上
        $count = $value['count'] ?? 0;
        // ユーザーの旅行の中で、写真枚数がN枚以上のものがあるか
        return $user->trips()->withCount('photos')->having('photos_count', '>=', $count)->exists();
    }
}
