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

        $query = Post::with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->withCount(['replies', 'reactions as likes_count' => function ($query) {
                $query->where('type', 'like');
            }, 'reactions as funs_count' => function ($query) {
                $query->where('type', 'fun');
            }])
            ->withExists(['reactions as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'like');
            }, 'reactions as is_fun' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'fun');
            }]);

        if ($tab === 'my_posts') {
            $query->where('user_id', $userId)->whereNull('parent_post_id');
        } elseif ($tab === 'my_replies') {
            $query->where('user_id', $userId)->whereNotNull('parent_post_id');
        } else {
            // timeline (default): Global posts, excluding replies
            $query->whereNull('parent_post_id');
        }

        $posts = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Timeline/Index', [
            'posts' => $posts,
            'currentTab' => $tab,
        ]);
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
            'type' => 'required|in:like,fun',
        ]);

        $type = $validated['type'];
        $user = Auth::user();

        // 既にリアクションしているか確認
        $existingReaction = $post
            ->reactions()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        if ($existingReaction) {
            // 既にある場合は削除 (Toggle Off)
            $existingReaction->delete();
        } else {
            // ない場合は作成 (Toggle On)
            $post->reactions()->create([
                'user_id' => $user->id,
                'type' => $type,
            ]);
        }

        return back();
    }

    public function show(Post $post)
    {
        $userId = Auth::id();

        // メインの投稿
        $post
            ->load(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->loadCount(['replies', 'reactions as likes_count' => function ($query) {
                $query->where('type', 'like');
            }, 'reactions as funs_count' => function ($query) {
                $query->where('type', 'fun');
            }])
            ->loadExists(['reactions as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'like');
            }, 'reactions as is_fun' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'fun');
            }]);

        // 親投稿 (もしあれば)
        if ($post->parentPost) {
            $post
                ->parentPost
                ->load(['user', 'attachment'])
                ->loadCount(['replies', 'reactions as likes_count' => function ($query) {
                    $query->where('type', 'like');
                }, 'reactions as funs_count' => function ($query) {
                    $query->where('type', 'fun');
                }])
                ->loadExists(['reactions as is_liked' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('type', 'like');
                }, 'reactions as is_fun' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)->where('type', 'fun');
                }]);
        }

        // 返信一覧
        $replies = $post
            ->replies()
            ->with(['user', 'attachment', 'parentPost.user', 'parentPost.attachment'])
            ->withCount(['replies', 'reactions as likes_count' => function ($query) {
                $query->where('type', 'like');
            }, 'reactions as funs_count' => function ($query) {
                $query->where('type', 'fun');
            }])
            ->withExists(['reactions as is_liked' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'like');
            }, 'reactions as is_fun' => function ($query) use ($userId) {
                $query->where('user_id', $userId)->where('type', 'fun');
            }])
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

        return response()->json([
            'trips' => $user->trips()->latest()->take(10)->get(),
            'photos' => \App\Models\Photo::whereIn('trip_id', $user->trips()->pluck('id'))->latest()->take(20)->get(),
            'suggestions' => $user->suggestions()->latest()->take(10)->get(),
        ]);
    }
}
