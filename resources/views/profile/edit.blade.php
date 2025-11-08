@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- 共通：成功メッセージ --}}
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif
            {{-- 共通：バリデーションエラー --}}
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
                <div class="card-header">マイページ編集</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT') {{-- 更新はPUTメソッドを使います --}}

                        {{-- 名前 --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">名前 *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name"
                                value="{{ old('name', $user->name) }}" required>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        {{-- メールアドレス --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">メールアドレス *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="email" name="email"
                                value="{{ old('email', $user->email) }}" required>
                            @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        <hr>
                        <p class="text-muted">パスワードを変更する場合のみ入力してください</p>

                        {{-- パスワード --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">新しいパスワード</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                id="password" name="password">
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>

                        {{-- パスワード確認 --}}
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">新しいパスワード（確認用）</label>
                            <input type="password" class="form-control"
                                id="password_confirmation" name="password_confirmation">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            更新する
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection