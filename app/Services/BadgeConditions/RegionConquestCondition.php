<?php

namespace App\Services\BadgeConditions;

use App\Models\User;

class RegionConquestCondition implements ConditionInterface
{
    public function check(User $user, array $value): bool
    {
        // N都道府県制覇
        // バッジのタイプが prefecture_conquest_count の場合と region_conquest の場合があるかもしれないが
        // ここでは region_conquest として実装し、汎用的に使う

        // case 'prefecture_conquest_count': // 単純な数
        if (isset($value['count']) && !isset($value['prefectures'])) {
            $visitedCount = $user->trips()->pluck('prefecture')->flatten()->unique()->count();
            return $visitedCount >= ($value['count'] ?? 0);
        }

        // case 'region_conquest': // 特定の県リスト
        $targetPrefectures = $value['prefectures'] ?? [];
        if (empty($targetPrefectures)) {
            return false;
        }

        // ユーザーが行ったことのある県を一意に取得
        $visitedPrefectures = $user
            ->trips()
            ->get()
            ->pluck('prefecture')
            ->flatten()
            ->unique()
            ->toArray();

        // 全てのターゲット県が含まれているかチェック
        foreach ($targetPrefectures as $pref) {
            if (!in_array($pref, $visitedPrefectures)) {
                return false;
            }
        }
        return true;
    }
}
