@extends('layouts.app') {{-- layouts.app を継承 (ナビバーなどが使える) --}}

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8 text-center">

            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h2>夫婦の思い出アルバム</h2>
                </div>
                <div class="card-body p-5">
                    <p class="lead">ようこそ！</p>
                    <p>旅行の思い出を、写真やメモと共に記録していきましょう。</p>
                    <hr class="my-4">
                    <p>このサービスを利用するにはログインが必要です。</p>

                    {{-- ログイン・登録ボタンを大きく表示 --}}
                    <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4 me-md-2">
                            ログイン
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-success btn-lg px-4">
                            新規登録
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection