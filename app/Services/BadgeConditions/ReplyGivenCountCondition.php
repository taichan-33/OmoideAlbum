<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class ReplyGivenCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 返信した回数
        // Postモデルで parent_post_id があるものをカウント
        // Userモデルにpostsリレーションが必要
        return $user->posts()->whereNotNull('parent_post_id')->count() >= ($value['count'] ?? 0);
    }
}
