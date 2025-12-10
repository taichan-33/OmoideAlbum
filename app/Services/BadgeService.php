<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\User;
use App\Services\BadgeConditions\AiSuggestionReactionCountCondition;
use App\Services\BadgeConditions\AiSuggestionVisitedCountCondition;
use App\Services\BadgeConditions\ConditionInterface;
use App\Services\BadgeConditions\KeywordCountCondition;
use App\Services\BadgeConditions\LongTripCountCondition;
use App\Services\BadgeConditions\ManualTripCountCondition;
use App\Services\BadgeConditions\MonthlyActiveStreakCondition;
use App\Services\BadgeConditions\MonthlyTripCountCondition;
use App\Services\BadgeConditions\ReactionGivenCountCondition;
use App\Services\BadgeConditions\RegionConquestCondition;
use App\Services\BadgeConditions\ReplyGivenCountCondition;
use App\Services\BadgeConditions\ShortTripCountCondition;
use App\Services\BadgeConditions\StatusUpdateCountCondition;
use App\Services\BadgeConditions\TagCountCondition;
use App\Services\BadgeConditions\TotalPhotoCountCondition;
use App\Services\BadgeConditions\TripPhotoCountCondition;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    /**
     * 条件タイプと処理クラスのマッピング
     * @var array<string, string>
     */
    protected array $conditionStrategies = [
        'monthly_trip_count' => MonthlyTripCountCondition::class,
        'prefecture_conquest_count' => RegionConquestCondition::class,  // 同一クラスで処理
        'region_conquest' => RegionConquestCondition::class,
        'short_trip_count' => ShortTripCountCondition::class,
        'long_trip_count' => LongTripCountCondition::class,
        'total_photo_count' => TotalPhotoCountCondition::class,
        'trip_photo_count' => TripPhotoCountCondition::class,
        'monthly_active_streak' => MonthlyActiveStreakCondition::class,
        'keyword_count' => KeywordCountCondition::class,
        'ai_suggestion_visited_count' => AiSuggestionVisitedCountCondition::class,
        'ai_suggestion_reaction_count' => AiSuggestionReactionCountCondition::class,
        'manual_trip_count' => ManualTripCountCondition::class,
        'reaction_given_count' => ReactionGivenCountCondition::class,
        'reply_given_count' => ReplyGivenCountCondition::class,
        'status_update_count' => StatusUpdateCountCondition::class,
        'tag_count' => TagCountCondition::class,
    ];

    public function checkBadges(User $user)
    {
        $newBadges = [];

        // 獲得していないバッジを取得
        $unearnedBadges = Badge::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($unearnedBadges as $badge) {
            if ($this->shouldAward($user, $badge)) {
                $this->awardBadge($user, $badge);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    /**
     * 条件クラスを使って判定を行う
     */
    protected function shouldAward(User $user, Badge $badge): bool
    {
        $type = $badge->condition_type;
        $class = $this->conditionStrategies[$type] ?? null;

        if (!$class || !class_exists($class)) {
            // 未実装の条件タイプの場合はfalse
            return false;
        }

        /** @var ConditionInterface $strategy */
        $strategy = new $class();

        return $strategy->check($user, $badge->condition_value ?? []);
    }

    protected function awardBadge(User $user, Badge $badge)
    {
        // 1. バッジ付与
        $user->badges()->attach($badge->id, ['obtained_at' => now()]);

        // 2. Bot取得 (Userモデルの共通メソッドを利用)
        $botUser = User::getBot() ?? $user;

        // 3. 通知ジョブ (タイムライン投稿)
        \App\Jobs\GenerateBadgeNotification::dispatch($user, $badge, $botUser);

        // 4. プッシュ通知
        $user->notify(new \App\Notifications\BadgeEarned($badge));
    }
}
