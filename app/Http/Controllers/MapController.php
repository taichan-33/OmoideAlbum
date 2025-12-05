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
            ->select('prefecture', 'start_date')
            ->orderBy('start_date', 'asc')
            ->get();

        $mapData = [];
        foreach ($trips as $trip) {
            $code = $this->convertPrefectureToCode($trip->prefecture);
            if ($code) {
                if (!isset($mapData[$code])) {
                    $mapData[$code] = [
                        'count' => 0,
                        'dates' => []
                    ];
                }
                $mapData[$code]['count']++;
                $mapData[$code]['dates'][] = $trip->start_date->format('Y/m/d');
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

        // 3. Inertiaレスポンスを返す
        return Inertia::render('Map/Index', [
            'mapData' => $mapData,
            'pinnedLocations' => $pinnedLocations
        ]);
    }

    /**
     * ピンを保存する
     */
    public function storePin(Request $request)
    {
        $request->validate([
            'prefecture_code' => 'required|string|starts_with:JP-'
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
            'prefecture_code' => 'required|string|starts_with:JP-'
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
        $normalizedName = str_replace(['県', '府', '都'], '', $name);

        $map = [
            '北海道' => 'JP-01',
            '青森' => 'JP-02',
            '岩手' => 'JP-03',
            '宮城' => 'JP-04',
            '秋田' => 'JP-05',
            '山形' => 'JP-06',
            '福島' => 'JP-07',
            '茨城' => 'JP-08',
            '栃木' => 'JP-09',
            '群馬' => 'JP-10',
            '埼玉' => 'JP-11',
            '千葉' => 'JP-12',
            '東京' => 'JP-13',
            '神奈川' => 'JP-14',
            '新潟' => 'JP-15',
            '富山' => 'JP-16',
            '石川' => 'JP-17',
            '福井' => 'JP-18',
            '山梨' => 'JP-19',
            '長野' => 'JP-20',
            '岐阜' => 'JP-21',
            '静岡' => 'JP-22',
            '愛知' => 'JP-23',
            '三重' => 'JP-24',
            '滋賀' => 'JP-25',
            '京都' => 'JP-26',
            '大阪' => 'JP-27',
            '兵庫' => 'JP-28',
            '奈良' => 'JP-29',
            '和歌山' => 'JP-30',
            '鳥取' => 'JP-31',
            '島根' => 'JP-32',
            '岡山' => 'JP-33',
            '広島' => 'JP-34',
            '山口' => 'JP-35',
            '徳島' => 'JP-36',
            '香川' => 'JP-37',
            '愛媛' => 'JP-38',
            '高知' => 'JP-39',
            '福岡' => 'JP-40',
            '佐賀' => 'JP-41',
            '長崎' => 'JP-42',
            '熊本' => 'JP-43',
            '大分' => 'JP-44',
            '宮崎' => 'JP-45',
            '鹿児島' => 'JP-46',
            '沖縄' => 'JP-47',
        ];

        return $map[$normalizedName] ?? null;
    }
}
