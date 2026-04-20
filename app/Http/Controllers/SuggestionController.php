<?php

namespace App\Http\Controllers;

use App\Actions\GenerateSuggestionAction;
use App\Actions\StoreSuggestionFromChatAction;
use App\Http\Requests\StoreSuggestionFromChatRequest;
use App\Http\Requests\StoreSuggestionRequest;
use App\Http\Requests\SuggestionIndexRequest;
use App\Models\Suggestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    /**
     * AI旅行提案の一覧を表示 (★検索機能付きに改修)
     */
    public function index(SuggestionIndexRequest $request): Response
    {
        $filters = $request->validated();

        $suggestions = Auth::user()
            ->suggestions()
            ->searchKeyword($filters['keyword'] ?? null)
            ->fromSource($filters['source'] ?? 'all')
            ->sortByOption($filters['sort'] ?? 'created_at_desc')
            ->get();

        return Inertia::render('Suggestions/Index', [
            'suggestions' => $suggestions,
            'filters' => [
                'keyword' => $filters['keyword'] ?? null,
                'source' => $filters['source'] ?? 'all',
                'sort' => $filters['sort'] ?? 'created_at_desc',
            ],
        ]);
    }

    /**
     * AIに新しい旅行提案を生成させ、保存する (★プロンプトを大幅強化)
     */
    public function store(StoreSuggestionRequest $request, GenerateSuggestionAction $generateSuggestion): RedirectResponse
    {
        try {
            $generateSuggestion(Auth::user(), $request->validated());

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
        $this->ensureOwnedByAuthenticatedUser($suggestion);

        return Inertia::render('Suggestions/Show', [
            'suggestion' => $suggestion,
        ]);
    }

    /**
     * 訪問済みステータスを切り替える
     */
    public function toggleStatus(Suggestion $suggestion): RedirectResponse
    {
        $this->ensureOwnedByAuthenticatedUser($suggestion);

        $suggestion->is_visited = ! $suggestion->is_visited;
        $suggestion->save();

        return redirect()->back()->with('success', 'ステータスを更新しました。');
    }

    /**
     * AI旅行提案を削除
     */
    public function destroy(Suggestion $suggestion): RedirectResponse
    {
        $this->ensureOwnedByAuthenticatedUser($suggestion);

        $suggestion->delete();

        return redirect()
            ->route('suggestions.index')
            ->with('success', '提案を削除しました。');
    }

    /**
     * チャットから提案を保存する
     */
    public function storeFromChat(
        StoreSuggestionFromChatRequest $request,
        StoreSuggestionFromChatAction $storeSuggestionFromChat
    ): RedirectResponse {
        $storeSuggestionFromChat(Auth::user(), $request->validated());

        return redirect()->back()->with('success', 'プランを保存しました！');
    }

    private function ensureOwnedByAuthenticatedUser(Suggestion $suggestion): void
    {
        if ($suggestion->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
