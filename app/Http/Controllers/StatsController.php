<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(): Response
    {
        // 1. 基本統計
        $totalTrips = Trip::count();
        $totalNights = Trip::sum('nights');

        // 2. よく行く都道府県 (Top 5)
        // prefectureカラムはJSON配列なので、少し工夫が必要。
        // MySQL 5.7+ / 8.0+ なら JSON_TABLE が使えるが、
        // ここではシンプルに全件取得してPHP側で集計するか、
        // あるいは単純化して「メインの行き先」として扱うか。
        // Tripモデルで prefecture は array キャストされている。

        // PHP側で集計するアプローチ (データ量が少なければこれで十分)
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
        $topPrefectures = array_slice($prefectureCounts, 0, 5, true);  // Top 5

        // 3. よく使うタグ (Top 5)
        $topTags = Tag::withCount('trips')
            ->orderBy('trips_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($tag) => [
                'name' => $tag->name,
                'count' => $tag->trips_count,
            ]);

        // 4. 月別旅行回数 (季節性)
        $monthlyCounts = Trip::select(DB::raw('MONTH(start_date) as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // 1~12月の配列を埋める
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyData[] = $monthlyCounts[$i] ?? 0;
        }

        // 5. 年別推移
        $yearlyCounts = Trip::select(DB::raw('YEAR(start_date) as year'), DB::raw('count(*) as count'))
            ->groupBy('year')
            ->orderBy('year')
            ->get()
            ->map(fn($item) => [
                'year' => $item->year,
                'count' => $item->count,
            ]);

        // 6. 写真が多い旅行 (Top 5)
        $topPhotoTrips = Trip::withCount('photos')
            ->orderBy('photos_count', 'desc')
            ->take(5)
            ->get()
            ->map(fn($trip) => [
                'id' => $trip->id,
                'title' => $trip->title,
                'photos_count' => $trip->photos_count,
            ]);

        return Inertia::render('Stats/Index', [
            'stats' => [
                'totalTrips' => $totalTrips,
                'totalNights' => $totalNights,
                'topPrefectures' => $topPrefectures,
                'topTags' => $topTags,
                'monthlyData' => $monthlyData,
                'yearlyCounts' => $yearlyCounts,
                'topPhotoTrips' => $topPhotoTrips,
            ]
        ]);
    }
}
