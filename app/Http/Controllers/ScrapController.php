<?php

namespace App\Http\Controllers;

use App\Models\Scrap;
use App\Services\OgpService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScrapController extends Controller
{
    protected $ogpService;

    public function __construct(OgpService $ogpService)
    {
        $this->ogpService = $ogpService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $scraps = auth()->user()->scraps()->latest()->get();
        return Inertia::render('Scraps/Index', [
            'scraps' => $scraps,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = $request->input('url');
        $ogpData = $this->ogpService->fetch($url);

        $scrap = auth()->user()->scraps()->create([
            'url' => $url,
            'title' => $ogpData['title'] ?? null,
            'description' => $ogpData['description'] ?? null,
            'image_url' => $ogpData['image_url'] ?? null,
            'site_name' => $ogpData['site_name'] ?? null,
            'metadata' => $ogpData,
        ]);

        return redirect()->back()->with('success', 'スクラップを追加しました！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scrap $scrap)
    {
        if ($scrap->user_id !== auth()->id()) {
            abort(403);
        }

        $scrap->delete();

        return redirect()->back()->with('success', 'スクラップを削除しました。');
    }
}
