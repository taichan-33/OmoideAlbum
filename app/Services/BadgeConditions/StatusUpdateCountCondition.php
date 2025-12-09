<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class StatusUpdateCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // TODO: 実装 (履歴機能が必要)
        return false;
    }
}
