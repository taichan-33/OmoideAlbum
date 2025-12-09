<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

interface ConditionInterface
{
    /**
     * 条件を満たしているか判定する
     *
     * @param User $user 判定対象ユーザー
     * @param array $value バッジに設定された閾値（例: ['count' => 5]）
     * @return bool
     */
    public function check(User $user, array $value): bool;
}
