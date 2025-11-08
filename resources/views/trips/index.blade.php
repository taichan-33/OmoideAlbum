@extends('layouts.app')

@section('styles')
<style>
    /* （Trix Editorのスタイルはそのまま） */
    .trix-content {
        line-height: 1.5;
    }

    .trix-content h1 {
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
    }

    .trix-content ul,
    .trix-content ol {
        padding-left: 2rem;
    }

    .trix-content blockquote {
        border-left: 4px solid #ccc;
        padding-left: 1rem;
        margin-left: 0.5rem;
        font-style: italic;
    }

    .trix-content pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }

    /* プレビュー用の高さ制限 */
    .trix-preview {
        max-height: 7rem;
        overflow: hidden;
        position: relative;
        -webkit-mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
        mask-image: linear-gradient(to bottom, black 70%, transparent 100%);
    }

    /* ★★★ (追加) リスト表示用のスタイル ★★★ */
    .list-group-item .trix-preview {
        max-height: 3rem;
        /* リスト表示ではプレビューを浅くする */
        font-size: 0.9rem;
        -webkit-mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
        mask-image: linear-gradient(to bottom, black 60%, transparent 100%);
    }
</style>
@endsection


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">

            {{-- 成功メッセージ --}}
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif

            {{-- 検索フォーム (変更なし) --}}
            <div class="accordion mb-3" id="searchAccordion">
                {{-- ... (検索フォームの中身はそのまま) ... --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseOne"
                            aria-expanded="false" aria-controls="collapseOne">
                            <i class="bi bi-search me-2"></i>
                            絞り込み・並び替え
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse {{ request()->anyFilled(['sort', 'prefecture', 'tag_id', 'date_from', 'date_to']) ? 'show' : '' }}"
                        aria-labelledby="headingOne" data-bs-parent="#searchAccordion">
                        <div class="card-body">
                            <form method="GET" action="{{ route('trips.index') }}">
                                <div class="row g-3">
                                    {{-- 並び替え --}}
                                    <div class="col-md-6">
                                        <label for="sort" class="form-label">並び替え</label>
                                        <select name="sort" id="sort" class="form-select">
                                            <option value="start_date_desc" {{ request('sort', 'start_date_desc') == 'start_date_desc' ? 'selected' : '' }}>
                                                開始日が新しい順
                                            </option>
                                            <option value="start_date_asc" {{ request('sort') == 'start_date_asc' ? 'selected' : '' }}>
                                                開始日が古い順
                                            </option>
                                            <option value="created_at_desc" {{ request('sort') == 'created_at_desc' ? 'selected' : '' }}>
                                                登録日が新しい順
                                            </option>
                                        </select>
                                    </div>

                                    {{-- 都道府県 --}}
                                    <div class="col-md-6">
                                        <label for="prefecture" class="form-label">都道府県</label>
                                        <input type="text" name="prefecture" id="prefecture"
                                            class="form-control"
                                            value="{{ request('prefecture') }}"
                                            placeholder="（例：兵庫県）">
                                    </div>

                                    {{-- タグ検索 --}}
                                    <div class="col-md-6">
                                        <label for="tag_id" class="form-label">タグ</label>
                                        <select name="tag_id" id="tag_id" class="form-select">
                                            <option value="">すべてのタグ</option>
                                            @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}" {{ request('tag_id') == $tag->id ? 'selected' : '' }}>
                                                {{ $tag->name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- 日付検索 (範囲) --}}
                                    <div class="col-md-6">
                                        <label class="form-label">日付（開始日）</label>
                                        <div class="input-group">
                                            <input type="date" name="date_from" class="form-control"
                                                value="{{ request('date_from') }}">
                                            <span class="input-group-text">〜</span>
                                            <input type="date" name="date_to" class="form-control"
                                                value="{{ request('date_to') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 text-end">
                                    <a href="{{ route('trips.index') }}" class="btn btn-secondary">
                                        リセット
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-search"></i> 検索する
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 一覧カード --}}
            <div class="card">
                {{-- ★★★ (修正) カードヘッダーに切り替えボタンを追加 ★★★ --}}
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>
                        思い出アルバム一覧
                        <a href="{{ route('trips.create') }}" class="btn btn-primary btn-sm ms-2">
                            <i class="bi bi-plus-lg"></i> 新しい旅行を記録
                        </a>
                    </span>

                    {{-- 表示切り替えボタン --}}
                    <div class="btn-group" role="group" aria-label="View Toggle">
                        <button type="button" class="btn btn-outline-secondary" id="btn-view-grid" title="グリッド表示">
                            <i class="bi bi-grid-3x3-gap-fill"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary" id="btn-view-list" title="リスト表示">
                            <i class="bi bi-list-task"></i>
                        </button>
                    </div>
                </div>
                {{-- ★★★ (修正) ここまで ★★★ --}}


                <div class="card-body">
                    @if($trips->isEmpty())
                    {{-- (データが無い場合の表示は変更なし) --}}
                    <p class="text-center">まだ旅行の記録がありません。</p>
                    @if(request()->anyFilled(['sort', 'prefecture', 'tag_id', 'date_from', 'date_to']))
                    <p class="text-center">
                        <a href="{{ route('trips.index') }}" class="btn btn-outline-secondary">
                            検索条件をリセットする
                        </a>
                    </p>
                    @else
                    <p class="text-center">
                        <a href="{{ route('trips.create') }}" class="btn btn-primary">
                            二人の最初の思い出を記録しよう！
                        </a>
                    </p>
                    @endif
                    @else

                    {{-- ★★★ 1. グリッド表示（既存） ★★★ --}}
                    <div id="view-grid">
                        <div class="row">
                            @foreach ($trips as $trip)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title">{{ $trip->title }}</h5>
                                        <h6 class="card-subtitle mb-2 text-muted">
                                            {{ $trip->start_date }} ({{ $trip->nights }}泊)
                                        </h6>

                                        @if($trip->tags->isNotEmpty())
                                        <div class="mb-2">
                                            @foreach ($trip->tags as $tag)
                                            <span class="badge bg-primary me-1">{{ $tag->name }}</span>
                                            @endforeach
                                        </div>
                                        @endif

                                        <p class="card-text">
                                            <strong>場所:</strong> {{ $trip->prefecture }}
                                        </p>

                                        <div class="card-text trix-content trix-preview" style="white-space: normal;">
                                            @if($trip->description)
                                            {!! $trip->description !!}
                                            @endif
                                        </div>

                                    </div>
                                    <div class="card-footer bg-transparent border-0 text-end">
                                        <a href="{{ route('trips.show', $trip) }}" class="btn btn-outline-primary btn-sm">
                                            詳細を見る
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div> {{-- /#view-grid --}}


                    {{-- ★★★ 2. リスト表示（新規追加） ★★★ --}}
                    <div id="view-list" class="d-none"> {{-- デフォルトは d-none で隠す --}}
                        <ul class="list-group list-group-flush">
                            @foreach ($trips as $trip)
                            <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                                <div class="col-12 col-md-8">
                                    <h5 class="mb-1">{{ $trip->title }}</h5>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> {{ $trip->start_date }} ({{ $trip->nights }}泊)
                                        <span class="mx-2">|</span>
                                        <i class="bi bi-geo-alt-fill"></i> {{ $trip->prefecture }}
                                    </small>

                                    @if($trip->tags->isNotEmpty())
                                    <div class="mt-1">
                                        @foreach ($trip->tags as $tag)
                                        <span class="badge bg-primary me-1">{{ $tag->name }}</span>
                                        @endforeach
                                    </div>
                                    @endif

                                    @if($trip->description)
                                    <div class="card-text trix-content trix-preview mt-2" style="white-space: normal;">
                                        {!! $trip->description !!}
                                    </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-4 text-md-end mt-2 mt-md-0">
                                    <a href="{{ route('trips.show', $trip) }}" class="btn btn-outline-primary btn-sm">
                                        詳細を見る
                                    </a>
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div> {{-- /#view-list --}}


                    {{-- ページネーション (変更なし) --}}
                    <div class="mt-4 d-flex justify-content-center">
                        {{ $trips->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


{{-- ★★★ 3. JavaScript のロジック（新規追加） ★★★ --}}
@push('scripts')
<script>
    // ページが読み込まれたら実行
    document.addEventListener('DOMContentLoaded', function() {
        const viewGrid = document.getElementById('view-grid');
        const viewList = document.getElementById('view-list');
        const btnGrid = document.getElementById('btn-view-grid');
        const btnList = document.getElementById('btn-view-list');

        // LocalStorage から 'view_mode' を読み込む
        const currentView = localStorage.getItem('view_mode') || 'grid'; // デフォルトは 'grid'

        // 表示を切り替える関数
        function setView(mode) {
            if (mode === 'list') {
                viewGrid.classList.add('d-none'); // グリッドを隠す
                viewList.classList.remove('d-none'); // リストを表示

                btnList.classList.add('active'); // リストボタンをアクティブに
                btnGrid.classList.remove('active');

                localStorage.setItem('view_mode', 'list'); // 選択を記憶
            } else {
                viewGrid.classList.remove('d-none'); // グリッドを表示
                viewList.classList.add('d-none'); // リストを隠す

                btnGrid.classList.add('active'); // グリッドボタンをアクティブに
                btnList.classList.remove('active');

                localStorage.setItem('view_mode', 'grid'); // 選択を記憶
            }
        }

        // ボタンのクリックイベント
        btnGrid.addEventListener('click', function() {
            setView('grid');
        });

        btnList.addEventListener('click', function() {
            setView('list');
        });

        // ページ読み込み時に、記憶していたビューを復元
        setView(currentView);
    });
</script>
@endpush