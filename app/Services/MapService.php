<?php

namespace App\Services;

use App\Enums\Prefecture;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MapService
{
    /**
     * 地図表示用のデータを取得する
     */
    public function getMapData(User $user): array
    {
        $trips = $user
            ->trips()
            ->with('photos')
            ->select('id', 'prefecture', 'start_date')
            ->orderBy('start_date', 'asc')
            ->get();

        $mapData = [];

        foreach ($trips as $trip) {
            $prefectures = is_array($trip->prefecture) ? $trip->prefecture : [$trip->prefecture];

            foreach ($prefectures as $prefName) {
                if (!is_string($prefName))
                    continue;

                $code = $this->convertPrefectureToCode($prefName);

                if ($code) {
                    if (!isset($mapData[$code])) {
                        $mapData[$code] = ['count' => 0, 'dates' => []];
                    }
                    $mapData[$code]['count']++;
                    $mapData[$code]['dates'][] = $trip->start_date->format('Y/m/d');

                    if (!isset($mapData[$code]['thumbnail']) && $trip->photos->isNotEmpty()) {
                        $mapData[$code]['thumbnail'] = Storage::url($trip->photos->first()->path);
                    }
                }
            }
        }
        return $mapData;
    }

    /**
     * ピン留めされた場所を取得する
     */
    public function getPinnedLocations()
    {
        return DB::table('pinned_locations')
            ->join('users', 'pinned_locations.user_id', '=', 'users.id')
            ->select('pinned_locations.prefecture_code', 'users.name', 'users.id as user_id')
            ->get()
            ->groupBy('prefecture_code')
            ->map(function ($group) {
                return [
                    'users' => $group->pluck('name')->unique()->values(),
                    'user_ids' => $group->pluck('user_id')->unique()->values(),
                    'has_me' => $group->contains('user_id', Auth::id()),
                ];
            });
    }

    /**
     * 保存された提案（AIプランナー）を取得する
     */
    public function getSavedSuggestions(User $user)
    {
        return $user
            ->suggestions()
            ->select('id', 'title', 'prefecture_code', 'is_visited')
            ->whereNotNull('prefecture_code')
            ->get()
            ->groupBy('prefecture_code');
    }

    /**
     * 都道府県名をコードに変換する
     */
    private function convertPrefectureToCode(string $name): ?string
    {
        $enumVal = Prefecture::fromName($name)?->value;
        if ($enumVal) {
            return $enumVal;
        }

        if (preg_match('/^[A-Z]{3}$/', $name)) {
            return $name;
        }

        return null;
    }
}
