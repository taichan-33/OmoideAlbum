<?php

namespace App\Services;

use App\Models\Tag;
use App\Models\Trip;
use Illuminate\Support\Facades\DB;

class StatsService
{
    public function getDashboardStats(): array
    {
        return [
            'totalTrips' => Trip::count(),
            'totalNights' => Trip::sum('nights'),
            'topPrefectures' => $this->getTopPrefectures(),
            'topTags' => $this->getTopTags(),
            'monthlyData' => $this->getMonthlyData(),
            'yearlyCounts' => $this->getYearlyCounts(),
            'topPhotoTrips' => $this->getTopPhotoTrips(),
        ];
    }

    private function getTopPrefectures()
    {
        // MySQL 8.0+ の JSON_TABLE を使用して高速化
        // prefectureカラム（JSON配列）を展開して集計する
        $sql = "
            SELECT p.name, COUNT(*) as count
            FROM trips,
            JSON_TABLE(prefecture, '\$[*]' COLUMNS (name VARCHAR(255) PATH '\$')) as p
            GROUP BY p.name
            ORDER BY count DESC
            LIMIT 5
        ";

        try {
            $results = DB::select($sql);

            $topPrefectures = [];
            foreach ($results as $row) {
                $topPrefectures[$row->name] = $row->count;
            }
            return $topPrefectures;
        } catch (\Exception $e) {
            // JSON_TABLE が使えない環境（MySQL 5.7以下など）のためのフォールバック
            // または開発環境でデータが入っていない場合など
            return $this->getTopPrefecturesFallback();
        }
    }

    private function getTopPrefecturesFallback()
    {
        $allTrips = Trip::select('prefecture')->get();
        $prefectureCounts = [];
        foreach ($allTrips as $trip) {
            $prefs = is_array($trip->prefecture) ? $trip->prefecture : [$trip->prefecture];
            foreach ($prefs as $pref) {
                if (!isset($prefectureCounts[$pref])) {
                    $prefectureCounts[$pref] = 0;
                }
                $prefectureCounts[$pref]++;
            }
        }
        arsort($prefectureCounts);
        return array_slice($prefectureCounts, 0, 5, true);
    }

    private function getTopTags()
    {
        return Tag::withCount('trips')
            ->orderBy('trips_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($tag) => [
                'name' => $tag->name,
                'count' => $tag->trips_count,
            ]);
    }

    private function getMonthlyData()
    {
        $monthlyCounts = Trip::select(DB::raw('MONTH(start_date) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $monthlyCounts[$i] ?? 0;
        }
        return $monthlyData;
    }

    private function getYearlyCounts()
    {
        return Trip::select(DB::raw('YEAR(start_date) as year'), DB::raw('count(*) as count'))
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(fn($item) => [
                'year' => $item->year,
                'count' => $item->count,
            ]);
    }

    private function getTopPhotoTrips()
    {
        return Trip::withCount('photos')
            ->orderBy('photos_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($trip) => [
                'id' => $trip->id,
                'title' => $trip->title,
                'photos_count' => $trip->photos_count,
            ]);
    }
}
