<?php

namespace App\Http\Controllers;

use App\Models\Trip;
// Request を使うために use
use Illuminate\Http\Request;
// 認証済みユーザーを取得するために use
use Illuminate\Support\Facades\Auth;
// View を返すために use
use Illuminate\View\View;
// Redirect を返すために use
use Illuminate\Http\RedirectResponse;
use App\Models\Tag;  // ★ Tagモデルを use
use Illuminate\Support\Facades\Storage;


class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // ログインユーザーの旅行クエリを準備 (ベースクエリ)
        $query = Auth::user()->trips();

        // ------------------------------------
        // ▼▼▼ 検索ロジック ▼▼▼
        // ------------------------------------

        // 1. 都道府県検索 (部分一致)
        if ($request->filled('prefecture')) {
            // 'LIKE' を使って部分一致検索
            $query->where('prefecture', 'LIKE', '%' . $request->prefecture . '%');
        }

        // 2. タグ検索 (関連テーブル)
        if ($request->filled('tag_id')) {
            // 'tags' リレーションが存在し、かつ
            // その 'tag_id' がリクエストと一致する旅行を検索
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('tag_id', $request->tag_id);
            });
        }

        // 3. 日付範囲検索 (開始日)
        // From (〜から)
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        // To (〜まで)
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }

        // ------------------------------------
        // ▼▼▼ ソート（並び替え）ロジック ▼▼▼
        // ------------------------------------
        $sort = $request->input('sort', 'start_date_desc'); // デフォルト

        if ($sort === 'start_date_asc') {
            $query->orderBy('start_date', 'asc');
        } elseif ($sort === 'created_at_desc') {
            $query->orderBy('created_at', 'desc');
        } else {
            // デフォルト (start_date_desc)
            $query->orderBy('start_date', 'desc');
        }

        // ------------------------------------
        // ▼▼▼ データ取得 ▼▼▼
        // ------------------------------------

        // 検索条件・ソート順を適用したクエリでページネーション
        $trips = $query->paginate(10);

        // 検索フォーム用に「すべてのタグ」を取得
        $tags = Tag::all();

        // 'trips.index' ビューに $trips と $tags を渡す
        return view('trips.index', compact('trips', 'tags', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // ★ 全てのタグを取得してビューに渡す
        $tags = Tag::all();
        return view('trips.create', compact('tags'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. バリデーション（ルールを定義）
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'prefecture' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'nights' => 'required|integer|min:0',
            'description' => 'nullable|string',
            // ★ tagsは配列で、中身が既存のtag_idであること
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id', // 配列の各要素がtagsテーブルのidとして存在すること
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:4096', // 各写真が画像ファイルで最大5MB
        ]);

        // 1. 旅行情報を保存
        $trip = $request->user()->trips()->create([
            'title' => $validated['title'],
            'prefecture' => $validated['prefecture'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'nights' => $validated['nights'],
            'description' => $validated['description'],
        ]);

        // 2. タグを紐付ける (attach)
        // tagsキーが存在し、かつ配列である場合のみ処理
        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $trip->tags()->attach($validated['tags']);
        }

        // 3. 写真のアップロードと保存
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('trip_photos', 'public'); // 'public'ディスクに保存
                $trip->photos()->create(['path' => $path]);
            }
        }

        return redirect()->route('trips.index')
            ->with('success', '旅行の思い出を登録しました！');
    }

    public function show(Trip $trip): View
    {
        // ログイン中のユーザーIDと、表示しようとしている$tripのuser_idが
        // 一致するかチェック (他人に見せないため)
        if ($trip->user_id !== Auth::id()) {
            abort(403); // 403 Forbidden (アクセス権がありません)
        }

        // 関連する写真も一緒に読み込む (N+1問題対策)
        $trip->load('photos');

        return view('trips.show', compact('trip'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip): View
    {
        // ログイン中のユーザーの旅行かチェック (他人の旅行は編集できない)
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $tags = Tag::all(); // ★ 全てのタグを取得
        return view('trips.edit', compact('trip', 'tags')); // ★ $tags をビューに渡す
    }

    /**
     * 旅行を更新
     */
    public function update(Request $request, Trip $trip): RedirectResponse
    {
        // ログイン中のユーザーの旅行かチェック
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'prefecture' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'nights' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'photos' => 'nullable|array',
            'photos.*' => 'image|max:4096',
            'delete_photos' => 'nullable|array', // 削除する写真のID配列
            'delete_photos.*' => 'exists:photos,id', // 存在する写真IDであること
        ]);

        // 1. 旅行情報を更新
        $trip->update([
            'title' => $validated['title'],
            'prefecture' => $validated['prefecture'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'nights' => $validated['nights'],
            'description' => $validated['description'],
        ]);

        // 2. タグの紐付けを同期 (sync)
        // syncは、現在の紐付けを解除し、指定されたタグで新しい紐付けを作成
        $trip->tags()->sync($validated['tags'] ?? []); // tagsがnullの場合は空配列を渡す

        // 3. 写真の削除処理
        if (isset($validated['delete_photos'])) {
            foreach ($validated['delete_photos'] as $photoIdToDelete) {
                $photo = $trip->photos()->find($photoIdToDelete);
                if ($photo) {
                    Storage::disk('public')->delete($photo->path); // ストレージからファイルを削除
                    $photo->delete(); // データベースのエントリを削除
                }
            }
        }

        // 4. 新しい写真のアップロードと保存
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('trip_photos', 'public');
                $trip->photos()->create(['path' => $path]);
            }
        }

        return redirect()->route('trips.show', $trip) // 編集後は詳細ページへリダイレクト
            ->with('success', '旅行の思い出を更新しました！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        // ログイン中のユーザーの旅行かチェック
        if ($trip->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // 関連する写真をストレージから削除
        foreach ($trip->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        // 旅行を削除 (関連する写真、タグの紐付けも自動で削除される)
        $trip->delete();

        return redirect()->route('trips.index')
            ->with('success', '旅行の思い出を削除しました。');
    }
}
