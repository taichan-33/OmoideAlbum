<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class BadgeService
{
    public function checkBadges(User $user)
    {
        $newBadges = [];

        // 獲得していないバッジを取得
        $unearnedBadges = Badge::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->get();

        foreach ($unearnedBadges as $badge) {
            if ($this->checkCondition($user, $badge)) {
                $this->awardBadge($user, $badge);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    // ... checkCondition ...

    protected function checkCondition(User $user, Badge $badge)
    {
        $type = $badge->condition_type;
        $value = $badge->condition_value;

        switch ($type) {
            case 'monthly_trip_count':
                // 月にN回以上旅行したか
                // 直近の月で判定（簡易実装）
                $currentMonthTrips = $user
                    ->trips()
                    ->whereMonth('start_date', now()->month)
                    ->whereYear('start_date', now()->year)
                    ->count();
                return ($currentMonthTrips ?? 0) >= ($value['count'] ?? 0);

            case 'prefecture_conquest_count':
                // N都道府県制覇
                $visitedCount = $user->trips()->pluck('prefecture')->flatten()->unique()->count();
                return $visitedCount >= ($value['count'] ?? 0);

            case 'short_trip_count':
                // N泊以下の旅行回数
                $nights = $value['nights'] ?? 1;
                $count = $value['count'] ?? 0;
                $shortTrips = $user->trips()->where('nights', '<=', $nights)->count();
                return $shortTrips >= $count;

            case 'long_trip_count':
                // N泊以上の旅行回数
                $nights = $value['nights'] ?? 3;
                $count = $value['count'] ?? 0;
                $longTrips = $user->trips()->where('nights', '>=', $nights)->count();
                return $longTrips >= $count;

            case 'total_photo_count':
                // 総写真枚数
                // User -> photos relation (hasManyThrough)
                return $user->photos()->count() >= ($value['count'] ?? 0);

            case 'trip_photo_count':
                // 1回の旅行でN枚以上
                $count = $value['count'] ?? 0;
                // ユーザーの旅行の中で、写真枚数がN枚以上のものがあるか
                return $user->trips()->withCount('photos')->having('photos_count', '>=', $count)->exists();

            case 'monthly_active_streak':
                // 過去Nヶ月連続でアクティブ（旅行or写真）
                // 簡易実装: 過去Nヶ月の各月に旅行があるかチェック
                $months = $value['months'] ?? 12;
                for ($i = 0; $i < $months; $i++) {
                    $date = now()->subMonths($i);
                    $exists = $user
                        ->trips()
                        ->whereMonth('start_date', $date->month)
                        ->whereYear('start_date', $date->year)
                        ->exists();
                    if (!$exists)
                        return false;
                }
                return true;

            case 'keyword_count':
                // キーワードが含まれる旅行回数
                $keyword = $value['keyword'] ?? '';
                $count = $value['count'] ?? 0;
                $matchedTrips = $user
                    ->trips()
                    ->where(function ($query) use ($keyword) {
                        $query
                            ->where('title', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%");
                    })
                    ->count();
                return $matchedTrips >= $count;

            case 'ai_suggestion_visited_count':
                // AI提案を行って「行った」にした回数
                // Suggestionモデルが必要
                return $user
                    ->suggestions()
                    ->where('source', 'ai')  // AI提案
                    ->where('is_visited', true)
                    ->count() >= ($value['count'] ?? 0);

            case 'ai_suggestion_reaction_count':
                // AI提案へのリアクション回数
                // Suggestionモデルにreactionsがあるか、あるいはReactionモデルでsuggestion_idを見るか
                // ここでは簡易的にSuggestionモデルにreactionカラムがあると仮定、またはReactionテーブルを集計
                // 現状のDB構造に合わせて調整が必要。一旦スキップまたは仮実装
                return false;  // TODO: 実装

            case 'manual_trip_count':
                // 手動登録の旅行回数
                // Tripモデルにsourceカラムがない場合、AI経由かどうかの判別が難しい
                // 一旦「全ての旅行」で代用するか、sourceカラムを追加する必要がある
                return $user->trips()->count() >= ($value['count'] ?? 0);

            case 'reaction_given_count':
                // リアクションした回数
                // Reactionモデルが必要。User -> reactions
                // まだUserにreactionsリレーションがないかもしれない
                return false;  // TODO: 実装

            case 'reply_given_count':
                // 返信した回数
                // Postモデルで parent_id があるものをカウント
                return $user->posts()->whereNotNull('parent_post_id')->count() >= ($value['count'] ?? 0);

            case 'status_update_count':
                // ステータス更新回数
                // 履歴を持っていない場合は判定不可。現在のstatus_updated_atだけでは回数は不明。
                // 履歴テーブルがないので、一旦「現在のステータスが設定されているか」で代用するか、
                // あるいはこのバッジは「履歴機能実装後」にする
                return false;

            case 'tag_count':
                // 特定のタグがついた旅行にN回行ったか
                $tags = $value['tags'] ?? [$value['tag'] ?? ''];
                $count = $value['count'] ?? 0;

                // タグ名で検索
                return $user->trips()->whereHas('tags', function ($query) use ($tags) {
                    $query->whereIn('name', $tags);
                })->count() >= $count;

            case 'region_conquest':
                // 特定の地方（県リスト）を制覇したか
                $prefectures = $value['prefectures'] ?? [];
                if (empty($prefectures))
                    return false;

                // ユーザーが行ったことのある県を取得
                // TripモデルのprefectureはJSON配列（複数県またぐ場合あり）なので、
                // 単純なpluckだと配列の配列になる可能性がある
                // MySQLのJSON関数を使うか、PHPで処理するか
                // ここではPHPで処理
                $visitedPrefectures = $user->trips()->get()->pluck('prefecture')->flatten()->unique()->toArray();

                // 全ての県が含まれているかチェック
                foreach ($prefectures as $pref) {
                    if (!in_array($pref, $visitedPrefectures)) {
                        return false;
                    }
                }
                return true;

            default:
                return false;
        }
    }

    protected function awardBadge(User $user, Badge $badge)
    {
        // バッジ付与
        $user->badges()->attach($badge->id, ['obtained_at' => now()]);

        // Botユーザーを取得
        $botEmail = config('services.bot.email');
        $bot = User::where('email', $botEmail)->first();

        // メールで見つからない場合、名前で検索（フォールバック）
        if (!$bot) {
            $bot = User::where('name', 'クイックン')->first();
        }

        // それでも見つからない場合は、システム投稿として扱う（または何もしない）
        // Botが見つからない場合は、ユーザー自身からの投稿にするか、あるいはエラーにするか。
        // ここではBotが見つからない場合はユーザー自身にするが、Job内ではBot必須になるため、
        // Botが見つからない場合のハンドリングはJob側ではなくここで吸収するか、Jobに渡すBotをnull許容にするか。
        // 今回はBotは必ずいる前提（Seederで作成済み）とするが、念のためフォールバック。
        $botUser = $bot ?: $user;

        // AIによるお祝いメッセージ生成ジョブをディスパッチ
        \App\Jobs\GenerateBadgeNotification::dispatch($user, $badge, $botUser);
    }
}
