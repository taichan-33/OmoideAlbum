@extends('layouts.app')

{{-- ★★★ (修正) タイムラインとAI生成HTML用のCSSを強化 ★★★ --}}
@section('styles')
<style>
    /* Trix Editorの基本スタイル（ここでは不要だが、他のページとの互換性のために残す） */
    .trix-content {
        line-height: 1.5;
    }

    .trix-content h1,
    .trix-content h2,
    .trix-content h3 {
        font-size: 1.5rem;
        margin-top: 1rem;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .trix-content ul,
    .trix-content ol {
        padding-left: 2rem;
        margin-bottom: 1rem;
    }

    .trix-content blockquote {
        border-left: 4px solid #ccc;
        padding-left: 1rem;
        margin-left: 0.5rem;
        font-style: italic;
    }

    .trix-content table,
    .itinerary-table table {
        width: 100% !important;
        margin-bottom: 1rem;
        border-collapse: collapse !important;
    }

    .trix-content th,
    .trix-content td,
    .itinerary-table th,
    .itinerary-table td {
        border: 1px solid #dee2e6 !important;
        padding: 0.75rem !important;
        vertical-align: top !important;
    }

    .trix-content th,
    .itinerary-table th {
        background-color: #f8f9fa;
    }

    /* ★ (修正) AIが生成したリッチなHTML（提案の理由）用のコンテナ */
    /* Trixのスタイルをリセットし、Bootstrapのスタイルを適用させる */
    .ai-generated-content {
        background-color: #fdfdfd;
        border-radius: 5px;
        padding: 1.5rem;
        white-space: normal;
        /* pre-wrapをリセット */
    }

    .ai-generated-content> :first-child {
        margin-top: 0;
    }

    .ai-generated-content> :last-child {
        margin-bottom: 0;
    }

    /* 星評価（スターレーティング）用のCSS */
    .star-rating {
        color: #ffc107;
        font-size: 1.25rem;
    }

    .star-rating .bi-star-fill,
    .star-rating .bi-star {
        display: inline-block;
    }

    .star-rating .bi-star {
        color: #e9ecef;
    }

    /* タイムラインUI用のCSS */
    .itinerary-timeline {
        list-style: none;
        padding-left: 0;
        position: relative;
    }

    .itinerary-timeline:before {
        content: '';
        position: absolute;
        top: 10px;
        left: 15px;
        bottom: 10px;
        width: 4px;
        background: #e9ecef;
        border-radius: 2px;
    }

    .timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 1.5rem;
    }

    /* ★ (修正) タイムラインマーカー内のアイコンサイズを調整 */
    .timeline-marker {
        position: absolute;
        left: 0;
        top: 0;
        width: 34px;
        height: 34px;
        background: #0d6efd;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        /* ★ アイコンのサイズ */
        font-weight: bold;
        z-index: 2;
        border: 4px solid white;
    }

    .timeline-content {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1rem 1.25rem;
    }

    .timeline-content h6 {
        font-weight: bold;
        color: #0d6efd;
        margin-bottom: 0.25rem;
    }

    /* ローディングアニメーション */
    .loader-container {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        min-height: 200px;
        overflow: hidden;
    }

    .loader-plane {
        font-size: 4rem;
        color: #0d6efd;
        animation: fly 1.8s ease-in-out infinite;
    }

    #loading-text {
        font-size: 1.1rem;
        font-weight: 500;
        margin-top: 1.5rem;
        color: #6c757d;
    }

    @keyframes fly {
        0% {
            transform: translateX(-120px) translateY(30px) rotate(-30deg);
            opacity: 0;
        }

        30% {
            transform: translateX(0) translateY(0) rotate(0deg);
            opacity: 1;
        }

        70% {
            transform: translateX(0) translateY(0) rotate(0deg);
            opacity: 1;
        }

        100% {
            transform: translateX(120px) translateY(-30px) rotate(30deg);
            opacity: 0;
        }
    }
</style>
@endsection


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            {{-- (成功・エラーメッセージはそのまま) --}}
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
            @endif

            {{-- (提案生成カード、モーダル起動ボタン、隠しフォームはそのまま) --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-robot"></i> AI旅行プランナー</h5>
                </div>
                <div class="card-body text-center" id="planner-card-body" style="min-height: 200px;">
                    <p class="lead">これまでの旅行の思い出をAIが分析し、ご夫婦にぴったりの「次の旅行先」を提案します。</p>
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#suggestionModal">
                        <i class="bi bi-magic"></i> 新しい旅行プランを提案してもらう
                    </button>
                    <small class="text-muted mt-2 d-block">※ 過去の旅行データが多いほど、提案の精度が上がります。</small>
                </div>
                <form id="suggestion-form" action="{{ route('suggestions.store') }}" method="POST" class="d-none"
                    onsubmit="return confirm('過去の旅行データと追加リクエストをAIに送信し、新しい提案を作成しますか？（最大2分ほどかかります）');">
                    @csrf
                    <input type="hidden" name="optional_destination" id="hidden_optional_destination">
                    <input type="hidden" name="optional_season" id="hidden_optional_season">
                    <input type="hidden" name="optional_budget" id="hidden_optional_budget">
                    <input type="hidden" name="optional_interest" id="hidden_optional_interest">
                    <input type="hidden" name="optional_memo" id="hidden_optional_memo">
                </form>
            </div>

            {{-- (過去の提案一覧、検索フォームはそのまま) --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightbulb-fill"></i> AIからの提案一覧</h5>
                </div>
                <div class="card-body bg-light border-bottom">
                    <form method="GET" action="{{ route('suggestions.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-6">
                                <label for="keyword" class="form-label">キーワード検索</label>
                                <input type="text" name="keyword" id="keyword" class="form-control"
                                    value="{{ request('keyword') }}" placeholder="タイトルや内容...">
                            </div>
                            <div class="col-md-4">
                                <label for="sort" class="form-label">並び替え</label>
                                <select name="sort" id="sort" class="form-select">
                                    <option value="created_at_desc" {{ request('sort', 'created_at_desc') == 'created_at_desc' ? 'selected' : '' }}>
                                        作成日が新しい順
                                    </option>
                                    <option value="score_desc" {{ request('sort') == 'score_desc' ? 'selected' : '' }}>
                                        おすすめ度が高い順
                                    </option>
                                    <option value="score_asc" {{ request('sort') == 'score_asc' ? 'selected' : '' }}>
                                        おすすめ度が低い順
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> 検索
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    @if($suggestions->isEmpty())
                    <p class="text-center">
                        @if(request()->filled('keyword'))
                        検索条件に一致する提案はありませんでした。
                        @else
                        まだAIからの提案はありません。
                        @endif
                    </p>
                    @else
                    <div class="accordion" id="suggestionsAccordion">
                        @foreach($suggestions as $index => $suggestion)
                        <div class="accordion-item">
                            {{-- (アコーディオンボタン、星評価はそのまま) --}}
                            <h2 class="accordion-header" id="heading_{{ $suggestion->id }}">
                                <button class="accordion-button {{ $index == 0 && !request()->anyFilled(['keyword', 'sort']) ? '' : 'collapsed' }}" type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapse_{{ $suggestion->id }}"
                                    aria-expanded="{{ $index == 0 && !request()->anyFilled(['keyword', 'sort']) ? 'true' : 'false' }}"
                                    aria-controls="collapse_{{ $suggestion->id }}">
                                    <div class="d-flex justify-content-between w-100 pe-3 flex-wrap">
                                        <span class="fs-5 fw-bold me-3">{{ $suggestion->title }}</span>
                                        <div class="star-rating" title="おすすめ度: {{ $suggestion->recommendation_score }}/5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <=$suggestion->recommendation_score)
                                                <i class="bi bi-star-fill"></i>
                                                @else
                                                <i class="bi bi-star"></i>
                                                @endif
                                                @endfor
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse_{{ $suggestion->id }}"
                                class="accordion-collapse collapse {{ $index == 0 && !request()->anyFilled(['keyword', 'sort']) ? 'show' : '' }}"
                                aria-labelledby="heading_{{ $suggestion->id }}"
                                data-bs-parent="#suggestionsAccordion">
                                <div class="accordion-body">

                                    {{-- ★★★ (修正) 1. 日程表タイムライン（アイコンと日付を表示） ★★★ --}}
                                    @if(!empty($suggestion->itinerary_data))
                                    <h5><i class="bi bi-calendar-check"></i> モデル日程表</h5>
                                    <ul class="itinerary-timeline">
                                        @foreach($suggestion->itinerary_data as $item)
                                        <li class="timeline-item">
                                            {{-- (修正) AIが指定したアイコンを表示 --}}
                                            <div class="timeline-marker" title="{{ $item['day'] ?? '' }}">
                                                <i class="bi {{ $item['icon'] ?? 'bi-geo-alt-fill' }}"></i>
                                            </div>
                                            <div class="timeline-content">
                                                {{-- (修正) 「1日目」などを表示 --}}
                                                <span class="fw-bold text-muted d-block mb-1">{{ $item['day'] ?? '' }}</span>
                                                <h6>{{ $item['title'] ?? '未設定' }}</h6>
                                                <p class="mb-0">{{ $item['details'] ?? '詳細なし' }}</p>
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                    <hr>
                                    @endif
                                    {{-- ★★★ (修正) ここまで ★★★ --}}

                                    {{-- ★★★ (修正) 2. 詳細な理由（Trix CSSを解除） ★★★ --}}
                                    <h5><i class="bi bi-chat-left-text-fill"></i> 提案の理由</h5>
                                    <div class="ai-generated-content">
                                        {!! $suggestion->content !!}
                                    </div>

                                    {{-- 削除ボタン --}}
                                    <hr>
                                    <div class="text-end">
                                        <form action="{{ route('suggestions.destroy', $suggestion) }}" method="POST"
                                            onsubmit="return confirm('この提案を削除しますか？');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="bi bi-trash-fill"></i> この提案を削除
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div> {{-- /card --}}
        </div>
    </div>
</div>

{{-- (モーダルは変更なし) --}}
<div class="modal fade" id="suggestionModal" tabindex="-1" aria-labelledby="suggestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="suggestionModalLabel">
                    <i class="bi bi-robot"></i> AIへの追加リクエスト（任意）
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <p>AIにより詳細な希望を伝えると、提案の精度が上がります。空欄のままでも構いません。</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="form_optional_destination" class="form-label">1. 希望の行き先（例: 東北, 沖縄）</label>
                        <input type="text" class="form-control" id="form_optional_destination" placeholder="特になし">
                    </div>
                    <div class="col-md-6">
                        <label for="form_optional_season" class="form-label">2. 希望の季節（例: 次の夏）</label>
                        <input type="text" class="form-control" id="form_optional_season" placeholder="特になし">
                    </div>
                    <div class="col-md-6">
                        <label for="form_optional_budget" class="form-label">3. 予算感</label>
                        <select class="form-select" id="form_optional_budget">
                            <option value="">指定なし</option>
                            <option value="リッチな旅行">リッチな旅行</option>
                            <option value="普通の旅行">普通の旅行</option>
                            <option value="節約ぎみの旅行">節約ぎみの旅行</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="form_optional_interest" class="form-label">4. 主な目的（例: グルメ, のんびり）</label>
                        <input type="text" class="form-control" id="form_optional_interest" placeholder="特になし">
                    </div>
                    <div class="col-12">
                        <label for="form_optional_memo" class="form-label">5. その他特記事項（自由記述）</label>
                        <textarea class="form-control" id="form_optional_memo" rows="3" placeholder="例：運転はしたくない、記念日です"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <button type="button" class="btn btn-primary" id="modal-submit-button">
                    <i class="bi bi-magic"></i> この内容で提案を生成する
                </button>
            </div>
        </div>
    </div>
</div>
@endsection


{{-- (JavaScriptは変更なし) --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalSubmitButton = document.getElementById('modal-submit-button');
        const mainForm = document.getElementById('suggestion-form');
        const modalEl = document.getElementById('suggestionModal');
        const plannerCardBody = document.getElementById('planner-card-body');

        if (!modalSubmitButton || !mainForm || !modalEl || !plannerCardBody) {
            console.error('AIプランナー: 必要なHTML要素が見つかりません。');
            return;
        }

        let suggestionModalInstance;
        try {
            suggestionModalInstance = new bootstrap.Modal(modalEl);
        } catch (e) {
            console.error('Bootstrapの初期化に失敗しました。bootstrap.js が正しく読み込まれているか確認してください。', e);
        }

        modalSubmitButton.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> 送信中...';

            let dest = document.getElementById('form_optional_destination').value;
            let season = document.getElementById('form_optional_season').value;
            let budget = document.getElementById('form_optional_budget').value;
            let interest = document.getElementById('form_optional_interest').value;
            let memo = document.getElementById('form_optional_memo').value;

            document.getElementById('hidden_optional_destination').value = dest;
            document.getElementById('hidden_optional_season').value = season;
            document.getElementById('hidden_optional_budget').value = budget;
            document.getElementById('hidden_optional_interest').value = interest;
            document.getElementById('hidden_optional_memo').value = memo;

            if (suggestionModalInstance) {
                suggestionModalInstance.hide();
            } else {
                console.warn('Bootstrap modal.hide() を実行できませんでした。');
            }

            plannerCardBody.innerHTML = `
                <div class="loader-container">
                    <i class="bi bi-airplane-fill loader-plane"></i>
                    <div id="loading-text">AIが最適な旅行計画を考え中です...</div>
                    <small class="text-muted">（最大2分ほどお待ちください）</small>
                </div>
            `;

            mainForm.submit();
        });

        modalEl.addEventListener('hidden.bs.modal', function() {
            modalSubmitButton.disabled = false;
            modalSubmitButton.innerHTML = '<i class="bi bi-magic"></i> この内容で提案を生成する';
        });
    });
</script>
@endpush