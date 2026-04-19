<?php

namespace App\Http\Controllers;

use App\Models\Trip;  // Tripモデルを使う
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoController extends Controller
{
    public function store(Request $request, Trip $trip): RedirectResponse
    {
        // 1. セキュリティチェック (他人の旅行にアップロードさせない)
        if ($trip->user_id !== Auth::id()) {
            abort(403);
        }

        // 2. バリデーション (photos配列を受け取るように変更)
        $validated = $request->validate([
            'photos' => 'required|array|max:50',  // 最大50枚まで
            'photos.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:10240',  // 1枚あたり10MBまで
            'caption' => 'nullable|string|max:1000',
        ]);

        $uploadedCount = 0;

        // 3. ファイルを保存 (ループ処理)
        if ($request->hasFile('photos')) {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());

            foreach ($request->file('photos') as $photoFile) {
                // 画像を読み込み、リサイズ (最大幅/高さ 1920px, アスペクト比維持)
                $image = $manager->read($photoFile);
                $image->scaleDown(width: 1920, height: 1920);

                // WebPに変換 (品質 80)
                $encoded = $image->toWebp(quality: 80);

                // ユニークなファイル名を生成
                $filename = 'photos/' . \Illuminate\Support\Str::uuid() . '.webp';

                // Storageに保存
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, (string) $encoded);

                // 4. データベースに記録
                $trip->photos()->create([
                    'path' => $filename,
                    'caption' => $validated['caption'],  // キャプションは全写真共通
                ]);
                $uploadedCount++;
            }
        }

        // 通知
        if ($uploadedCount > 0) {
            $user = Auth::user();
            $others = \App\Models\User::where('id', '!=', $user->id)->get();
            \Illuminate\Support\Facades\Notification::send($others, new \App\Notifications\TripUpdated(
                $trip,
                "{$user->name}さんが写真を{$uploadedCount}枚追加しました",
                route('trips.show', $trip->id),
                '📷'
            ));
        }

        // 5. 元の旅行詳細ページにリダイレクト
        return redirect()
            ->route('trips.show', $trip)
            ->with('success', "写真({$uploadedCount}枚)を追加しました！");
    }
}
