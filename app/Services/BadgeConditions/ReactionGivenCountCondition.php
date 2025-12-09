<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class ReactionGivenCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // TODO: 実装
        return false;
    }
}
