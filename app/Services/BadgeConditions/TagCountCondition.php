<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class TagCountCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // 特定のタグがついた旅行にN回行ったか
        $tags = $value['tags'] ?? [$value['tag'] ?? ''];
        $count = $value['count'] ?? 0;

        // タグ名で検索
        return $user->trips()->whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('name', $tags);
        })->count() >= $count;
    }
}
