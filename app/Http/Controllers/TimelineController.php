<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TimelineController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab = $request->input('tab', 'timeline');

        $posts = Post::with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->withReactionDetails($userId)
            ->filterByTab($tab, $userId)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        // Get statuses
        $currentUser = Auth::user();
        $partner = $currentUser->partner;

        return Inertia::render('Timeline/Index', [
            'posts' => $posts,
            'currentTab' => $tab,
            'userStatus' => $currentUser->only(['status', 'status_updated_at']),
            'partnerStatus' => $partner ? $partner->only(['name', 'status', 'status_updated_at']) : null,
        ]);
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $user->update([
            'status' => $validated['status'],
            'status_updated_at' => now(),
        ]);

        return back();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content' => 'nullable|string|max:1000',
            // 添付情報（オプション）
            'attachment_type' => 'nullable|string',  // "App\Models\Trip", "App\Models\Post" など
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

    public function toggleReaction(Request $request, Post $post)
    {
        $validated = $request->validate([
            'type' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\ReactionType::class)],
        ]);

        $post->toggleReaction(Auth::id(), $validated['type']);

        return back();
    }

    public function show(Post $post)
    {
        $userId = Auth::id();

        // メインの投稿
        $post = Post::withReactionDetails($userId)
            ->with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->findOrFail($post->id);

        // 親投稿 (もしあれば)
        if ($post->parent_post_id) {
            $post->setRelation('parentPost', Post::withReactionDetails($userId)->with(['user', 'attachment'])->find($post->parent_post_id));
        }

        // 返信一覧
        $replies = $post
            ->replies()
            ->withReactionDetails($userId)
            ->with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->latest()
            ->get();

        return Inertia::render('Timeline/Show', [
            'post' => $post,
            'replies' => $replies,
        ]);
    }

    public function getAttachables(Request $request)
    {
        $user = Auth::user();
        $tripId = $request->input('trip_id');

        $photosQuery = $user->photos();

        if ($tripId) {
            $photosQuery->where('trip_id', $tripId);
        }

        return response()->json([
            'trips' => $user->trips()->latest()->get(),  // Get all trips for dropdown
            'photos' => $photosQuery->latest()->take(20)->get(),
            'suggestions' => $user->suggestions()->latest()->take(10)->get(),
        ]);
    }
}
