<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // 1. 地理・制覇系
            [
                'name' => '日本一周の旅人',
                'description' => '47都道府県すべてに旅行した',
                'icon_path' => '🗾',
                'condition_type' => 'prefecture_conquest_count',
                'condition_value' => ['count' => 47],
            ],
            [
                'name' => '北の大地マスター',
                'description' => '北海道・東北地方のすべての県を制覇した',
                'icon_path' => '☃️',
                'condition_type' => 'region_conquest',
                'condition_value' => ['region' => '北海道・東北', 'prefectures' => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県']],
            ],
            [
                'name' => '南国リゾーター',
                'description' => '九州・沖縄地方のすべての県を制覇した',
                'icon_path' => '🌺',
                'condition_type' => 'region_conquest',
                'condition_value' => ['region' => '九州・沖縄', 'prefectures' => ['福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県']],
            ],
            [
                'name' => '弾丸トラベラー',
                'description' => '1泊2日以下の旅行が累計10回以上',
                'icon_path' => '🚄',
                'condition_type' => 'short_trip_count',
                'condition_value' => ['nights' => 1, 'count' => 10],
            ],
            [
                'name' => 'ロングバケーション',
                'description' => '3泊4日以上の旅行を達成した',
                'icon_path' => '🏖️',
                'condition_type' => 'long_trip_count',
                'condition_value' => ['nights' => 3, 'count' => 1],
            ],
            // 2. 写真・思い出系
            [
                'name' => '思い出ミリオネア',
                'description' => 'アプリ内の総写真枚数が1,000枚突破',
                'icon_path' => '📸',
                'condition_type' => 'total_photo_count',
                'condition_value' => ['count' => 1000],
            ],
            [
                'name' => 'シャッター・バグ',
                'description' => '1回の旅行で100枚以上の写真をアップロードした',
                'icon_path' => '🐛',
                'condition_type' => 'trip_photo_count',
                'condition_value' => ['count' => 100],
            ],
            [
                'name' => '思い出の守り人',
                'description' => '過去1年間、毎月1回以上は何らかの旅行または写真を記録している',
                'icon_path' => '🛡️',
                'condition_type' => 'monthly_active_streak',
                'condition_value' => ['months' => 12],
            ],
            // 3. タグ・趣向系
            [
                'name' => '温泉ソムリエ',
                'description' => '温泉タグのついた旅行に5回行った',
                'icon_path' => '♨️',
                'condition_type' => 'tag_count',
                'condition_value' => ['tag' => '温泉', 'count' => 5],
            ],
            [
                'name' => '絶景ハンター',
                'description' => '絶景または自然タグのついた旅行に10回行った',
                'icon_path' => '🏔️',
                'condition_type' => 'tag_count',
                'condition_value' => ['tags' => ['絶景', '自然'], 'count' => 10],
            ],
            [
                'name' => 'グルメタレント',
                'description' => 'グルメまたは食べ歩きタグのついた旅行に5回行った',
                'icon_path' => '🍽️',
                'condition_type' => 'tag_count',
                'condition_value' => ['tags' => ['グルメ', '食べ歩き'], 'count' => 5],
            ],
            [
                'name' => 'テーマパーク王',
                'description' => '遊園地またはテーマパークタグのついた旅行に3回行った',
                'icon_path' => '🎡',
                'condition_type' => 'tag_count',
                'condition_value' => ['tags' => ['遊園地', 'テーマパーク'], 'count' => 3],
            ],
            [
                'name' => '雨男・雨女カップル',
                'description' => '旅行のメモやタイトルに「雨」という文字が3回以上含まれている',
                'icon_path' => '☔️',
                'condition_type' => 'keyword_count',
                'condition_value' => ['keyword' => '雨', 'count' => 3],
            ],
            // 4. AI・計画系
            [
                'name' => 'AIシンクロ率100%',
                'description' => 'AIが提案したSuggestionのステータスを「行った」に3回変更した',
                'icon_path' => '🤖',
                'condition_type' => 'ai_suggestion_visited_count',
                'condition_value' => ['count' => 3],
            ],
            [
                'name' => 'プランナーの鏡',
                'description' => 'AI提案に対して「行きたい」リアクションを累計10回行った',
                'icon_path' => '📝',
                'condition_type' => 'ai_suggestion_reaction_count',
                'condition_value' => ['reaction' => 'want_to_go', 'count' => 10],
            ],
            [
                'name' => '気まぐれトラベラー',
                'description' => '手動登録の旅行が10回以上ある',
                'icon_path' => '🐈',
                'condition_type' => 'manual_trip_count',
                'condition_value' => ['count' => 10],
            ],
            // 5. 夫婦コミュニケーション系
            [
                'name' => '相思相愛',
                'description' => '相手の投稿に対して「いいね」を累計50回送った',
                'icon_path' => '❤️',
                'condition_type' => 'reaction_given_count',
                'condition_value' => ['reaction' => 'like', 'count' => 50],
            ],
            [
                'name' => '聞き上手',
                'description' => '相手の投稿に対して「返信」を累計20回送った',
                'icon_path' => '👂',
                'condition_type' => 'reply_given_count',
                'condition_value' => ['count' => 20],
            ],
            [
                'name' => 'ステータス更新マニア',
                'description' => '「今の気分」を累計10回更新した',
                'icon_path' => '🔄',
                'condition_type' => 'status_update_count',
                'condition_value' => ['count' => 10],
            ],
            // 既存のバッジも維持（重複チェックはfirstOrCreateで対応）
            [
                'name' => 'フットワーク軽夫婦',
                'description' => '月に2回以上旅行した',
                'icon_path' => '👟',
                'condition_type' => 'monthly_trip_count',
                'condition_value' => ['count' => 2],
            ],
            [
                'name' => '北陸マスター',
                'description' => '北陸地方（富山、石川、福井）を制覇した',
                'icon_path' => '🦀',
                'condition_type' => 'region_conquest',
                'condition_value' => ['region' => '北陸', 'prefectures' => ['富山県', '石川県', '福井県']],
            ],
        ];

        foreach ($badges as $badge) {
            \App\Models\Badge::firstOrCreate(
                ['name' => $badge['name']],
                $badge
            );
        }
    }
}
