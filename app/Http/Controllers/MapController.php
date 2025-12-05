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
    public function index(\App\Services\MapService $service): Response
    {
        return Inertia::render('Map/Index', [
            'mapData' => $service->getMapData(Auth::user()),
            'pinnedLocations' => $service->getPinnedLocations(),
            'savedSuggestions' => $service->getSavedSuggestions(Auth::user())
        ]);
    }

    /**
     * ピンを保存する
     */
    public function storePin(Request $request)
    {
        $request->validate([
            'prefecture_code' => 'required|string'
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
            'prefecture_code' => 'required|string'
        ]);

        DB::table('pinned_locations')
            ->where('user_id', Auth::id())
            ->where('prefecture_code', $request->prefecture_code)
            ->delete();

        return redirect()->back();
    }
}
