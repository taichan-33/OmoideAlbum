<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TagController extends Controller
{
    /**
     * タグ一覧と作成フォームを表示
     */
    public function index(): Response
    {
        // 全てのタグを作成日が新しい順に取得
        $tags = Tag::latest()->get();
        return Inertia::render('Tags/Index', [
            'tags' => $tags
        ]);
    }

    /**
     * 新しいタグを保存
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // 必須、文字列、最大50文字、tagsテーブルでユニーク(重複不可)
            'name' => 'required|string|max:50|unique:tags',
        ]);

        Tag::create($validated);

        return redirect()
            ->route('tags.index')
            ->with('success', '新しいタグを作成しました！');
    }

    /**
     * タグを削除
     */
    public function destroy(Tag $tag): RedirectResponse
    {
        // 関連する旅行（tag_trip）との紐付けは自動で削除される
        $tag->delete();

        return redirect()
            ->route('tags.index')
            ->with('success', 'タグを削除しました。');
    }
}
