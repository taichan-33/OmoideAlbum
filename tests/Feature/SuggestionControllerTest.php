<?php

namespace Tests\Feature;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SuggestionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_applies_source_keyword_and_sort_filters(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Suggestion::create([
            'user_id' => $user->id,
            'title' => '北海道グルメ旅',
            'content' => ['description' => '海鮮と温泉を楽しむプラン'],
            'recommendation_score' => 2,
            'itinerary_data' => [['day' => 1, 'spots' => []]],
            'source' => 'chat',
        ]);

        Suggestion::create([
            'user_id' => $user->id,
            'title' => '京都散策',
            'content' => ['description' => '寺社めぐり'],
            'recommendation_score' => 5,
            'itinerary_data' => [['day' => 1, 'spots' => []]],
            'source' => 'planner',
        ]);

        Suggestion::create([
            'user_id' => $otherUser->id,
            'title' => '北海道ドライブ',
            'content' => ['description' => '別ユーザーの提案'],
            'recommendation_score' => 5,
            'itinerary_data' => [['day' => 1, 'spots' => []]],
            'source' => 'chat',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('suggestions.index', [
                'keyword' => '北海道',
                'source' => 'chat',
                'sort' => 'score_desc',
            ]));

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Suggestions/Index')
            ->where('filters.keyword', '北海道')
            ->where('filters.source', 'chat')
            ->where('filters.sort', 'score_desc')
            ->has('suggestions', 1)
            ->where('suggestions.0.title', '北海道グルメ旅')
        );
    }

    public function test_store_from_chat_saves_suggestion_and_creates_pin(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('map.index'))
            ->post(route('suggestions.storeFromChat'), [
                'title' => '箱根温泉ゆったり旅',
                'content' => [
                    'title' => '箱根でのんびり',
                    'description' => '温泉と美術館を楽しむ1泊2日です。',
                    'points' => [],
                ],
                'accommodation' => '箱根湯本の旅館',
                'local_food' => '湯葉丼',
                'itinerary' => [
                    [
                        'day' => 1,
                        'spots' => [
                            ['time' => '10:00', 'name' => '箱根湯本', 'description' => '到着'],
                        ],
                    ],
                ],
                'prefecture_code' => 'JP-14',
            ]);

        $response->assertRedirect(route('map.index'));

        $this->assertDatabaseHas('suggestions', [
            'user_id' => $user->id,
            'title' => '箱根温泉ゆったり旅',
            'prefecture_code' => 'JP-14',
            'source' => 'chat',
            'recommendation_score' => 5,
        ]);

        $this->assertDatabaseHas('pinned_locations', [
            'user_id' => $user->id,
            'prefecture_code' => 'JP-14',
        ]);
    }
}
