<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimelineController extends Controller
{
    public function index()
    {
        // タイムライン取得（最新順）
        $posts = Post::with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Timeline/Index', [
            'posts' => $posts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:1000',
            // 添付情報（オプション）
            'attachment_type' => 'nullable|string',  // "App\Models\Trip" など
            'attachment_id' => 'nullable|integer',
            // リツイート元（オプション）
            'parent_post_id' => 'nullable|exists:posts,id',
        ]);

        // "本文なし" かつ "添付なし" かつ "リツイートなし" はNG
        if (empty($validated['content']) && empty($validated['attachment_id']) && empty($validated['parent_post_id'])) {
            return back()->withErrors(['content' => '投稿内容を入力してください。']);
        }

        $request->user()->posts()->create($validated);

        return back();  // 画面リロード
    }

    public function getAttachables(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'trips' => $user->trips()->latest()->take(10)->get(),
            'photos' => \App\Models\Photo::whereIn('trip_id', $user->trips()->pluck('id'))->latest()->take(20)->get(),
            'suggestions' => $user->suggestions()->latest()->take(10)->get(),
        ]);
    }
}
