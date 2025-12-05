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

        // 2. バリデーション
        $validated = $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',  // 必須、画像ファイル、5MBまで
            'caption' => 'nullable|string|max:1000',
        ]);

        // 3. ファイルを保存 ( storage/app/public/photos フォルダに保存 )
        // $path には 'photos/ユニークなファイル名.jpg' のようなパスが入る
        $path = $request->file('photo')->store('photos', 'public');

        // 4. データベースに記録 (Tripとの関連付け)
        $trip->photos()->create([
            'path' => $path,
            'caption' => $validated['caption'],
        ]);

        // 通知
        $user = Auth::user();
        $others = \App\Models\User::where('id', '!=', $user->id)->get();
        \Illuminate\Support\Facades\Notification::send($others, new \App\Notifications\TripUpdated(
            $trip,
            "{$user->name}さんが写真をアップロードしました",
            route('trips.show', $trip->id),
            '📷'
        ));

        // 5. 元の旅行詳細ページにリダイレクト
        return redirect()
            ->route('trips.show', $trip)
            ->with('success', '写真を追加しました！');
    }
}
