<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * Returns trips that have photos, to be displayed as stories.
     */
    public function index()
    {
        $user = auth()->user();

        // 写真がある旅のみ取得
        // 写真がある旅のみ取得
        $stories = auth()
            ->user()
            ->trips()
            ->has('photos')
            ->withCount('photos')
            ->with(['photos' => function ($query) {
                $query->latest()->limit(1);  // サムネイル用に最新1枚だけ
            }])
            ->inRandomOrder()
            ->take(1)
            ->get()
            ->map(function ($trip) {
                return [
                    'id' => $trip->id,
                    'title' => $trip->title,
                    'thumbnail_url' => $trip->photos->first()?->path
                        ? \Illuminate\Support\Facades\Storage::url($trip->photos->first()->path)
                        : null,
                    'date' => $trip->start_date->format('Y-m-d'),
                    'photo_count' => $trip->photos_count,
                ];
            });

        return response()->json($stories);
    }

    /**
     * Display the specified resource.
     * Returns the trip and its photos for the story viewer.
     */
    public function show(Trip $trip)
    {
        // 権限チェック
        if ($trip->user_id !== auth()->id()) {
            abort(403);
        }

        $trip->load(['photos' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);

        return response()->json([
            'id' => $trip->id,
            'title' => $trip->title,
            'start_date' => $trip->start_date->format('Y-m-d'),
            'end_date' => $trip->end_date->format('Y-m-d'),
            'photos' => $trip->photos->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => \Illuminate\Support\Facades\Storage::url($photo->path),
                    'caption' => $photo->caption,  // 'comment' ではなく 'caption' が正しいカラム名
                    'taken_at' => $photo->created_at->format('Y-m-d H:i'),  // taken_atがないのでcreated_atで代用
                    'location' => null,  // locationカラムはない
                ];
            })->values(),
        ]);
    }
}
