@extends('layouts.app')

@section('styles')
{{-- 写真削除カードのスタイル --}}
<style>
    .delete-photo-card {
        position: relative;
    }

    .delete-photo-card .delete-check {
        position: absolute;
        top: 5px;
        right: 10px;
        z-index: 10;
        transform: scale(1.5);
    }

    .delete-photo-card .card-img-top {
        height: 120px;
        object-fit: cover;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- 成功・エラーメッセージ --}}
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="card">
                {{-- ★ ここは「編集」用のヘッダーです --}}
                <div class="card-header">旅行を編集: {{ $trip->title }}</div>

                <div class="card-body">
                    {{-- ★ action は `update`、@method('PUT') が必要です --}}
                    <form method="POST" action="{{ route('trips.update', $trip) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- タイトル --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">タイトル *</label>
                            {{-- ★ value に `$trip->title` が入ります --}}
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title"
                                value="{{ old('title', $trip->title) }}" required>
                        </div>

                        {{-- 都道府県 --}}
                        <div class="mb-3">
                            <label for="prefecture" class="form-label">都道府県 *</label>
                            <input type="text" class="form-control @error('prefecture') is-invalid @enderror"
                                id="prefecture" name="prefecture"
                                value="{{ old('prefecture', $trip->prefecture) }}" required>
                        </div>

                        {{-- 日付 --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">開始日 *</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date"
                                    value="{{ old('start_date', $trip->start_date) }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">終了日</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date" name="end_date"
                                    value="{{ old('end_date', $trip->end_date) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nights" class="form-label">泊数 *</label>
                                <input type="number" class="form-control @error('nights') is-invalid @enderror"
                                    id="nights" name="nights"
                                    value="{{ old('nights', $trip->nights) }}" required min="0">
                            </div>
                        </div>

                        {{-- ★ メモ (Trix Editor) ★ --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">メモ</label>
                            {{-- ★ value に `$trip->description` をセットします --}}
                            <input id="description" type="hidden" name="description" value="{{ old('description', $trip->description) }}">
                            <trix-editor input="description" class="form-control @error('description') is-invalid @enderror" style="min-height: 150px;"></trix-editor>

                            @error('description')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        {{-- タグ選択フォーム --}}
                        <div class="mb-3">
                            <label class="form-label">タグ</label>
                            <div>
                                {{-- ★ 既存のタグ（$trip->tags）にチェックを入れます --}}
                                @php
                                $tripTagIds = old('tags', $trip->tags->pluck('id')->toArray());
                                @endphp

                                @forelse($tags as $tag)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox"
                                        id="tag_{{ $tag->id }}" name="tags[]"
                                        value="{{ $tag->id }}"
                                        {{ in_array($tag->id, $tripTagIds) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                </div>
                                @empty
                                <p class="text-muted">
                                    タグがまだ作成されていません。<a href="{{ route('tags.index') }}">タグ管理</a>から作成してください。
                                </p>
                                @endforelse
                            </div>
                        </div>

                        {{-- 現在の写真表示と削除 --}}
                        @if ($trip->photos->isNotEmpty())
                        <div class="mb-3">
                            <label class="form-label">現在の写真（削除する写真にチェック）</label>
                            <div class="row g-2">
                                @foreach ($trip->photos as $photo)
                                <div class="col-6 col-md-3">
                                    <div class="card delete-photo-card">
                                        <img src="{{ Storage::url($photo->path) }}" class="card-img-top" alt="旅行の写真">
                                        <div class="form-check delete-check">
                                            <input class="form-check-input bg-danger" type="checkbox"
                                                id="delete_photo_{{ $photo->id }}" name="delete_photos[]" value="{{ $photo->id }}">
                                            <label class="form-check-label" for="delete_photo_{{ $photo->id }}"></label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- 新しい写真のアップロード --}}
                        <div class="mb-3">
                            <label for="photos" class="form-label">新しい写真を追加（複数選択可）</label>
                            <input type="file" class="form-control @error('photos.*') is-invalid @enderror"
                                id="photos" name="photos[]" multiple accept="image/*">
                            @error('photos.*')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">更新する</button>
                        <a href="{{ route('trips.show', $trip) }}" class="btn btn-secondary">
                            詳細に戻る
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection