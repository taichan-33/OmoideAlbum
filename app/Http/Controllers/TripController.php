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
use App\Models\Tag;  // ★ Tagモデルを use
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // ログインユーザーの旅行クエリを準備 (ベースクエリ)
        // ★ 夫婦で共有するため、全ユーザーの旅行を取得するように変更
        $query = Trip::query()->with(['tags', 'photos']);

        // ------------------------------------
        // ▼▼▼ 検索ロジック (Scope利用) ▼▼▼
        // ------------------------------------
        $query->filter($request->all());

        // ------------------------------------
        // ▼▼▼ ソート（並び替え）ロジック ▼▼▼
        // ------------------------------------
        $sort = $request->input('sort', 'start_date_desc');

        if ($sort === 'start_date_asc') {
            $query->orderBy('start_date', 'asc');
        } elseif ($sort === 'created_at_desc') {
            $query->orderBy('created_at', 'desc');
        } else {
            $query->orderBy('start_date', 'desc');
        }

        // ------------------------------------
        // ▼▼▼ データ取得 ▼▼▼
        // ------------------------------------

        $trips = $query
            ->paginate(12)
            ->through(fn($trip) => [
                'id' => $trip->id,
                'title' => $trip->title,
                'prefecture' => $trip->prefecture,
                'start_date' => $trip->start_date,  // 日付フォーマットが必要ならここで
                'nights' => $trip->nights,
                'description' => $trip->description,
                'thumbnail' => $trip->photos->first() ? Storage::url($trip->photos->first()->path) : null,
                'tags' => $trip->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name]),
            ]);

        // ------------------------------------
        // ▼▼▼ あの日の思い出 (On This Day) ▼▼▼
        // ------------------------------------
        $today = \Carbon\Carbon::today();
        $onThisDayTrips = Trip::onThisDay()
            ->with('photos')
            ->get()
            ->map(fn($trip) => [
                'id' => $trip->id,
                'title' => $trip->title,
                'prefecture' => $trip->prefecture,
                'start_date' => $trip->start_date,
                'years_ago' => $today->year - $trip->start_date->year,
                'thumbnail' => $trip->photos->first() ? Storage::url($trip->photos->first()->path) : null,
            ]);

        return Inertia::render('Trips/Index', [
            'trips' => $trips,
            'filters' => $request->all(),
            'tags' => Tag::all(),
            'onThisDayTrips' => $onThisDayTrips,  // ビューに渡す
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // ★ 全てのタグを取得してビューに渡す
        $tags = Tag::all();
        return Inertia::render('Trips/Create', [
            'tags' => $tags
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(\App\Http\Requests\StoreTripRequest $request): RedirectResponse
    {
        // 1. バリデーション済みデータを取得
        $validated = $request->validated();

        // 1. 旅行情報を保存
        // ★ 夫婦共有のため、user_idは保存するが、表示制限はしない
        $trip = Trip::create([
            'user_id' => $request->user()->id,  // 作成者は記録しておく
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
                $path = $photoFile->store('trip_photos', 'public');  // 'public'ディスクに保存
                $trip->photos()->create(['path' => $path]);
            }
        }

        return redirect()
            ->route('trips.index')
            ->with('success', '旅行の思い出を登録しました！');
    }

    public function show(Trip $trip): Response
    {
        // ログイン中のユーザーIDと、表示しようとしている$tripのuser_idが
        // 一致するかチェック (他人に見せないため)
        // ★ 夫婦共有のため、このチェックは削除（またはコメントアウト）
        // if ($trip->user_id !== Auth::id()) {
        //     abort(403);  // 403 Forbidden (アクセス権がありません)
        // }

        // 関連する写真も一緒に読み込む (N+1問題対策)
        $trip->load(['photos.comments.user', 'tags', 'packingItems']);

        return Inertia::render('Trips/Show', [
            'trip' => [
                'id' => $trip->id,
                'title' => $trip->title,
                'prefecture' => $trip->prefecture,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'nights' => $trip->nights,
                'description' => $trip->description,
                'summary' => $trip->summary,
                'tags' => $trip->tags->map(fn($tag) => ['id' => $tag->id, 'name' => $tag->name]),
                'photos' => $trip->photos->map(fn($photo) => [
                    'id' => $photo->id,
                    'path' => Storage::url($photo->path),
                    'caption' => $photo->caption,
                    'comments' => $photo->comments->map(fn($comment) => [
                        'id' => $comment->id,
                        'user_id' => $comment->user_id,
                        'user_name' => $comment->user->name,
                        'comment' => $comment->comment,
                        'created_at' => $comment->created_at->format('Y/m/d H:i'),
                    ]),
                ]),
                'packing_items' => $trip->packingItems->map(fn($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'is_checked' => $item->is_checked,
                ]),
            ],
            'packing_templates' => config('packing.templates'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Trip $trip): Response
    {
        // ログイン中のユーザーの旅行かチェック (他人の旅行は編集できない)
        // ★ 夫婦共有のため、編集も許可する（必要なら別途権限管理）
        // if ($trip->user_id !== Auth::id()) {
        //     abort(403, 'Unauthorized action.');
        // }

        $tags = Tag::all();  // ★ 全てのタグを取得

        // 編集画面に必要なデータを整形して渡す
        return Inertia::render('Trips/Edit', [
            'trip' => [
                'id' => $trip->id,
                'title' => $trip->title,
                'prefecture' => $trip->prefecture,
                'start_date' => $trip->start_date,
                'end_date' => $trip->end_date,
                'nights' => $trip->nights,
                'description' => $trip->description,
                'tag_ids' => $trip->tags->pluck('id'),  // 選択済みタグIDの配列
                'photos' => $trip->photos->map(fn($photo) => [
                    'id' => $photo->id,
                    'path' => Storage::url($photo->path),
                ]),
            ],
            'tags' => $tags
        ]);
    }

    /**
     * 旅行を更新
     */
    public function update(\App\Http\Requests\UpdateTripRequest $request, Trip $trip): RedirectResponse
    {
        // ログイン中のユーザーの旅行かチェック
        // ★ 夫婦共有のため許可
        // if ($trip->user_id !== Auth::id()) {
        //     abort(403, 'Unauthorized action.');
        // }

        $validated = $request->validated();

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
        $trip->tags()->sync($validated['tags'] ?? []);  // tagsがnullの場合は空配列を渡す

        // 3. 写真の削除処理
        if (isset($validated['delete_photos'])) {
            foreach ($validated['delete_photos'] as $photoIdToDelete) {
                $photo = $trip->photos()->find($photoIdToDelete);
                if ($photo) {
                    Storage::disk('public')->delete($photo->path);  // ストレージからファイルを削除
                    $photo->delete();  // データベースのエントリを削除
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

        return redirect()
            ->route('trips.show', $trip)  // 編集後は詳細ページへリダイレクト
            ->with('success', '旅行の思い出を更新しました！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Trip $trip): RedirectResponse
    {
        // ログイン中のユーザーの旅行かチェック
        // ★ 夫婦共有のため許可
        // if ($trip->user_id !== Auth::id()) {
        //     abort(403, 'Unauthorized action.');
        // }

        // 関連する写真をストレージから削除
        foreach ($trip->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }

        // 旅行を削除 (関連する写真、タグの紐付けも自動で削除される)
        $trip->delete();

        return redirect()
            ->route('trips.index')
            ->with('success', '旅行の思い出を削除しました。');
    }
}
