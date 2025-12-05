<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class MapController extends Controller
{
    /**
     * 制覇マップを表示する
     */
    public function index(): Response
    {
        $user = Auth::user();

        // 1. 訪問履歴の集計 (日付も取得)
        // tripsテーブルからデータを取得し、都道府県ごとにグループ化
        $trips = $user
            ->trips()
            ->with('photos')  // Eager load photos
            ->select('id', 'prefecture', 'start_date')  // Need 'id' for relation
            ->orderBy('start_date', 'asc')
            ->get();

        $mapData = [];
        foreach ($trips as $trip) {
            // prefectureは配列 (Tripモデルのcastsにより)
            $prefectures = is_array($trip->prefecture) ? $trip->prefecture : [$trip->prefecture];

            foreach ($prefectures as $prefName) {
                if (!is_string($prefName))
                    continue;

                $code = $this->convertPrefectureToCode($prefName);
                if ($code) {
                    if (!isset($mapData[$code])) {
                        $mapData[$code] = [
                            'count' => 0,
                            'dates' => []
                        ];
                    }
                    $mapData[$code]['count']++;
                    // 同じ日付が重複しないようにチェックしても良いが、
                    // 訪問回数ベースならそのままでもOK。
                    // ここではシンプルに追加。
                    $mapData[$code]['dates'][] = $trip->start_date->format('Y/m/d');

                    // Add thumbnail if available (use the first photo of the first trip to that location, or overwrite?)
                    // Let's use the first photo found for that location.
                    if (!isset($mapData[$code]['thumbnail']) && $trip->photos->isNotEmpty()) {
                        $mapData[$code]['thumbnail'] = \Illuminate\Support\Facades\Storage::url($trip->photos->first()->path);
                    }
                }
            }
        }

        // 2. ピン留めした場所の取得 (ユーザー情報付き)
        $pinnedLocations = DB::table('pinned_locations')
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

        // 3. 保存されたプラン（提案）の取得
        $suggestions = $user
            ->suggestions()
            ->select('id', 'title', 'prefecture_code', 'is_visited')
            ->whereNotNull('prefecture_code')
            ->get()
            ->groupBy('prefecture_code');

        // 4. Inertiaレスポンスを返す
        return Inertia::render('Map/Index', [
            'mapData' => $mapData,
            'pinnedLocations' => $pinnedLocations,
            'savedSuggestions' => $suggestions
        ]);
    }

    /**
     * ピンを保存する
     */
    public function storePin(Request $request)
    {
        $request->validate([
            'prefecture_code' => 'required|string'  // Removed starts_with:JP- to allow ISO codes
        ]);

        DB::table('pinned_locations')->insertOrIgnore([
            'user_id' => Auth::id(),
            'prefecture_code' => $request->prefecture_code,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back();
    }

    /**
     * ピンを削除する
     */
    public function destroyPin(Request $request)
    {
        $request->validate([
            'prefecture_code' => 'required|string'  // Removed starts_with:JP-
        ]);

        DB::table('pinned_locations')
            ->where('user_id', Auth::id())
            ->where('prefecture_code', $request->prefecture_code)
            ->delete();

        return redirect()->back();
    }

    /**
     * 都道府県名（文字列）を ISOコード (JP-01 〜 JP-47) に変換する
     */
    private function convertPrefectureToCode(string $name): ?string
    {
        // Try to find in Enum first (Japan Prefectures)
        $enumVal = \App\Enums\Prefecture::fromName($name)?->value;
        if ($enumVal) {
            return $enumVal;
        }

        // If not found, check if it looks like an ISO code (3 letters)
        // This is a simple heuristic. You might want to validate against a list of countries if strictness is needed.
        if (preg_match('/^[A-Z]{3}$/', $name)) {
            return $name;
        }

        return null;
    }
}
