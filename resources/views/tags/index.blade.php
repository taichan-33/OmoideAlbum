@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- 成功メッセージ --}}
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif

            {{-- 1. 新規タグ作成フォーム --}}
            <div class="card mb-4">
                <div class="card-header">新しいタグを作成</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('tags.store') }}" class="d-flex">
                        @csrf
                        <input type="text"
                            name="name"
                            class="form-control me-2 @error('name') is-invalid @enderror"
                            placeholder="タグ名（例：記念日, 初めて）"
                            value="{{ old('name') }}"
                            required>
                        <button type="submit" class="btn btn-primary text-nowrap">作成</button>
                        @error('name')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </form>
                </div>
            </div>

            {{-- 2. タグ一覧 --}}
            <div class="card">
                <div class="card-header">タグ一覧</div>
                <div class="card-body">
                    @if($tags->isEmpty())
                    <p>タグはまだ作成されていません。</p>
                    @else
                    <ul class="list-group">
                        @foreach($tags as $tag)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ $tag->name }}

                            {{-- 削除ボタン --}}
                            <form method="POST" action="{{ route('tags.destroy', $tag) }}"
                                onsubmit="return confirm('本当に削除しますか？');">
                                @csrf
                                @method('DELETE')
                                <button type.submit" class="btn btn-danger btn-sm">削除</button>
                            </form>
                        </li>
                        @endforeach
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection