@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                {{-- ★★★ (修正) ここが「新しい旅行を記録」になっていることを確認 ★★★ --}}
                <div class="card-header">新しい旅行を記録</div>

                <div class="card-body">

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- ★★★ (修正) action が `route('trips.store')` で、@method('PUT') が「ない」ことを確認 ★★★ --}}
                    <form method="POST" action="{{ route('trips.store') }}" enctype="multipart/form-data">
                        @csrf

                        {{-- タイトル --}}
                        <div class="mb-3">
                            <label for="title" class="form-label">タイトル *</label>
                            {{-- ★★★ (修正) value が `old('title')` のみになっていることを確認 ★★★ --}}
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        {{-- 都道府県 --}}
                        <div class="mb-3">
                            <label for="prefecture" class="form-label">都道府県 *</label>
                            <input type="text" class="form-control @error('prefecture') is-invalid @enderror"
                                id="prefecture" name="prefecture" value="{{ old('prefecture') }}" required>
                        </div>

                        {{-- 日付 --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="start_date" class="form-label">開始日 *</label>
                                <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                    id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="end_date" class="form-label">終了日</label>
                                <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                    id="end_date" name="end_date" value="{{ old('end_date') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="nights" class="form-label">泊数 *</label>
                                <input type="number" class="form-control @error('nights') is-invalid @enderror"
                                    id="nights" name="nights" value="{{ old('nights', 0) }}" min="0" required>
                            </div>
                        </div>

                        {{-- メモ (Trix Editor) --}}
                        <div class="mb-3">
                            <label for="description" class="form-label">メモ</label>
                            {{-- ★★★ (修正) value が `old('description')` のみになっていることを確認 ★★★ --}}
                            <input id="description" type="hidden" name="description" value="{{ old('description') }}">
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
                                @forelse($tags as $tag)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox"
                                           id="tag_{{ $tag->id }}" name="tags[]"
                                           value="{{ $tag->id }}"
                                           {{ in_array($tag->id, old('tags', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tag_{{ $tag->id }}">{{ $tag->name }}</label>
                                </div>
                                @empty
                                    <p class="text-muted">
                                        タグがまだ作成されていません。<a href="{{ route('tags.index') }}">タグ管理</a>から作成してください。
                                    </p>
                                @endforelse
                            </div>
                            @error('tags.*')
                                <span class="text-danger" role="alert">
                                    <small><strong>{{ $message }}</strong></small>
                                </span>
                            @enderror
                        </div>

                        {{-- 写真アップロードフォーム --}}
                        <div class="mb-3">
                            <label for="photos" class="form-label">写真（複数選択可）</label>
                            <input type="file" class="form-control @error('photos.*') is-invalid @enderror"
                                   id="photos" name="photos[]" multiple accept="image/*">
                            @error('photos.*')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">登録する</button>
                        <a href="{{ route('trips.index') }}" class="btn btn-secondary">
                            一覧に戻る
                        </a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

ÏÏ