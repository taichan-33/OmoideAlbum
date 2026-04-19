<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSuggestionAction;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    /**
     * AI旅行提案の一覧を表示 (★検索機能付きに改修)
     */
    public function index(Request $request): Response
    {
        // ログイン中のユーザーの提案クエリを準備
        $query = Auth::user()->suggestions();

        // 1. キーワード検索 (タイトルと内容)
        if ($request->filled('keyword')) {
            $keyword = '%' . $request->keyword . '%';
            // title または content (TrixのHTML) の中を検索
            $query->where(function ($q) use ($keyword) {
                $q
                    ->where('title', 'LIKE', $keyword)
                    ->orWhere('content', 'LIKE', $keyword);
            });
        }

        // 2. ソースフィルタ (planner / chat)
        if ($request->filled('source') && $request->source !== 'all') {
            $query->where('source', $request->source);
        }

        // 3. 並び替え (デフォルトは新しい順)
        $sort = $request->input('sort', 'created_at_desc');

        if ($sort === 'score_desc') {
            $query->orderBy('recommendation_score', 'desc');
        } elseif ($sort === 'score_asc') {
            $query->orderBy('recommendation_score', 'asc');
        } else {
            // created_at_desc (デフォルト)
            $query->orderBy('created_at', 'desc');
        }

        // 検索・ソート結果を取得 (ページネーションは後でもOK)
        $suggestions = $query->get();

        return Inertia::render('Suggestions/Index', [
            'suggestions' => $suggestions,
            'filters' => $request->only(['keyword', 'sort']),
        ]);
    }

    /**
     * AIに新しい旅行提案を生成させ、保存する (★プロンプトを大幅強化)
     */
    public function store(Request $request, GenerateSuggestionAction $generateSuggestion): RedirectResponse
    {
        $validatedOptional = $request->validate([
            'optional_destination' => 'nullable|string|max:255',
            'optional_season' => 'nullable|string|max:255',
            'optional_budget' => 'nullable|string|max:255',
            'optional_interest' => 'nullable|string|max:255',
            'optional_memo' => 'nullable|string|max:500',
        ]);

        try {
            $generateSuggestion(Auth::user(), $validatedOptional);

            return redirect()
                ->route('suggestions.index')
                ->with('success', '新しい旅行プランが提案されました！');
        } catch (\Throwable $e) {
            return redirect()
                ->route('suggestions.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * 提案詳細を表示
     */
    public function show(Suggestion $suggestion): Response
    {
        if ($suggestion->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Suggestions/Show', [
            'suggestion' => $suggestion
        ]);
    }

    /**
     * 訪問済みステータスを切り替える
     */
    public function toggleStatus(Suggestion $suggestion): RedirectResponse
    {
        if ($suggestion->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $suggestion->is_visited = !$suggestion->is_visited;
        $suggestion->save();

        return redirect()->back()->with('success', 'ステータスを更新しました。');
    }

    /**
     * AI旅行提案を削除
     */
    public function destroy(Suggestion $suggestion): RedirectResponse
    {
        if ($suggestion->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $suggestion->delete();

        return redirect()
            ->route('suggestions.index')
            ->with('success', '提案を削除しました。');
    }

    /**
     * チャットから提案を保存する
     */
    public function storeFromChat(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'content' => 'required',  // string or array
            'accommodation' => 'nullable|string',
            'local_food' => 'nullable|string',
            'itinerary' => 'required|array',
            'prefecture_code' => 'required|string|starts_with:JP-',
        ]);

        Auth::user()->suggestions()->create([
            'title' => $validated['title'],
            'recommendation_score' => 5,  // チャットからの保存は高評価とみなす
            'content' => $validated['content'] ?? '',
            'accommodation' => $validated['accommodation'],
            'local_food' => $validated['local_food'],
            'itinerary_data' => $validated['itinerary'],
            'prefecture_code' => $validated['prefecture_code'],
            'source' => 'chat',
        ]);

        // ピン留めも自動で行う
        DB::table('pinned_locations')->insertOrIgnore([
            'user_id' => Auth::id(),
            'prefecture_code' => $validated['prefecture_code'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'プランを保存しました！');
    }
}
