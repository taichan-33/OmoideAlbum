<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Models\PhotoComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoCommentController extends Controller
{
    /**
     * 写真へのコメントを保存
     */
    public function store(Request $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $photo->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->back()->with('success', 'コメントを投稿しました！');
    }

    /**
     * コメントを削除
     */
    public function destroy(PhotoComment $comment): RedirectResponse
    {
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $comment->delete();

        return redirect()->back()->with('success', 'コメントを削除しました。');
    }
}
