@extends('layouts.app')

@section('styles')
<style>
    .gallery-photo-card .card-img-top {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease;
        border: 1px solid #eee;
    }

    .gallery-photo-card:hover .card-img-top {
        transform: scale(1.03);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .icon-label {
        display: inline-block;
        width: 1.75rem;
        text-align: center;
    }

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

            {{-- ★★★ (追加) エラーメッセージ ★★★ --}}
            @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
            @endif

            {{-- 1. 旅行の詳細 --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0">
                        <i class="bi bi-card-text"></i>
                        旅行の詳細
                    </h5>
                    <div>
                        <a href="{{ route('trips.index') }}" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> 一覧に戻る
                        </a>

                        <a href="{{ route('trips.edit', $trip) }}" class="btn btn-sm btn-info text-white me-2">
                            <i class="bi bi-pencil-fill"></i> 編集
                        </a>

                        <form action="{{ route('trips.destroy', $trip) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('本当にこの旅行の思い出を削除しますか？\n関連する写真もすべて削除されます。');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">
                                <i class="bi bi-trash-fill"></i> 削除
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card-body p-4">

                    <h3 class="card-title mb-3">{{ $trip->title }}</h3>

                    <h6 class="card-subtitle mb-3 text-muted fs-5">
                        <i class="bi bi-calendar3 icon-label"></i>
                        {{ $trip->start_date }} 〜 {{ $trip->end_date ?? $trip->start_date }} ({{ $trip->nights }}泊)
                    </h6>

                    <p class="card-text fs-5 mb-3">
                        <i class="bi bi-geo-alt-fill icon-label"></i>
                        <strong>場所:</strong> {{ $trip->prefecture }}
                    </p>

                    @if($trip->tags->isNotEmpty())
                    <div class="mb-3 fs-5">
                        <i class="bi bi-tags-fill icon-label align-top"></i>
                        <div class="d-inline-block">
                            @foreach ($trip->tags as $tag)
                            <span class="badge bg-primary me-1 mb-1 fs-6">{{ $tag->name }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <hr class="my-4">

                    <p class="card-text fs-5 mb-2">
                        <i class="bi bi-sticky-fill icon-label"></i>
                        <strong>メモ:</strong>
                    </p>
                    <div class="bg-light p-3 p-md-4 rounded fs-5 trix-content" style="white-space: normal;">
                        @if($trip->description)
                        {!! $trip->description !!}
                        @else
                        <p class="text-muted mb-0">メモはありません</p>
                        @endif
                    </div>
                </div>
            </div>


            {{-- ★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★ --}}
            {{-- ★★★ (追加) 1.5. AIによるハイライト（要約） ★★★ --}}
            {{-- ★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★ --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-robot"></i> AIによるハイライト
                    </h5>
                    {{-- 要約ボタン（フォーム） --}}
                    <form action="{{ route('trips.summarize', $trip) }}" method="POST"
                        onsubmit="return confirm('AIに要約を依頼します。メモの内容がAIに送信されますが、よろしいですか？');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            @if($trip->summary)
                            <i class="bi bi-arrow-repeat"></i> 再生成する
                            @else
                            <i class="bi bi-magic"></i> AIで要約する
                            @endif
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if($trip->summary)
                    {{-- 要約がすでにある場合は、それを表示 --}}
                    <p class="fs-5 fst-italic" style="white-space: pre-wrap;">" {{ $trip->summary }} "</p>
                    @else
                    {{-- まだない場合 --}}
                    <p class="text-muted">
                        まだAIによる要約はありません。<br>
                        「メモ」欄に50文字以上の思い出を記入してから、「AIで要約する」ボタンを押してください。
                    </p>
                    @endif
                </div>
            </div>
            {{-- ★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★ --}}
            {{-- ★★★ (追加) ここまで ★★★ --}}
            {{-- ★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★★ --}}


            {{-- 2. 写真アップロードフォーム --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header">
                    <i class="bi bi-camera-fill"></i> 写真を追加
                </div>
                <div class="card-body">
                    {{--
                      エラー表示を修正: 
                      写真アップロードのエラーは 'photos' という名前のエラーバッグに保存するように
                      PhotoController を将来的に修正するのを見越して、
                      ここでは $errors->any() ではなく $errors->hasBag('default') をチェック
                    --}}
                    @if ($errors->any() && $errors->hasBag('default'))
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('photos.store', $trip) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="photo" class="form-label">写真ファイル (5MBまで)</label>
                            <input class="form-control @error('photo') is-invalid @enderror"
                                type="file" id="photo" name="photo" required>
                            @error('photo')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="caption" class="form-label">写真のメモ（キャプション）</label>
                            <textarea class="form-control" id="caption" name="caption" rows="3">{{ old('caption') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> アップロードする
                        </button>
                    </form>
                </div>
            </div>

            {{-- 3. 写真ギャラリー --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <i class="bi bi-images"></i> ギャラリー
                </div>
                <div class="card-body">
                    @if($trip->photos->isEmpty())
                    <p class="text-center">この旅行にはまだ写真がありません。</p>
                    @else
                    <div class="row g-3">
                        @foreach($trip->photos as $photo)
                        <div class="col-md-4">
                            <div class="card gallery-photo-card shadow-sm h-100">
                                <img src="{{ Storage::url($photo->path) }}" class="card-img-top" alt="{{ $photo->caption ?? '旅行写真' }}">
                                @if($photo->caption)
                                <div class="card-body">
                                    <p class="card-text">{{ $photo->caption }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection