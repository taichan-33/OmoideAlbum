<?php

use App\Http\Controllers\AiSummaryController;  // ★ これを追加
use App\Http\Controllers\MapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\TripController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/vue-test', function () {
    return Inertia::render('Test');
});

Route::get('/', function () {
    // もしログイン済み(Auth::check())なら、旅行一覧ページにリダイレクト
    if (Auth::check()) {
        return redirect()->route('trips.index');
    }

    // 未ログインなら、専用の welcome ビューを表示
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// 旅行関連のルート
Route::resource('trips', TripController::class)->middleware('auth');  // 旅行のCRUD操作を提供

// PhotoControllerのstoreメソッドを呼ぶ
Route::post('/trips/{trip}/photos', [App\Http\Controllers\PhotoController::class, 'store'])
    ->name('photos.store')
    ->middleware('auth');

// タグ関連のルート
Route::resource('tags', App\Http\Controllers\TagController::class)
    ->only(['index', 'store', 'destroy'])  // 今回は一覧、保存、削除だけ使う
    ->middleware('auth');

// ログイン必須(auth)のグループとして定義
Route::middleware('auth')->group(function () {
    // 編集フォーム表示 (GET /profile)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // 更新処理 (PUT /profile)
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// --------------------------------------------------
// ★ AI要約用のルートをここに追加 ★
// --------------------------------------------------
Route::post('/trips/{trip}/summarize', [AiSummaryController::class, 'generate'])
    ->name('trips.summarize')
    ->middleware('auth');

Route::post('/suggestions/from-chat', [SuggestionController::class, 'storeFromChat'])
    ->name('suggestions.storeFromChat')
    ->middleware('auth');

Route::resource('suggestions', SuggestionController::class)
    ->middleware('auth');  // ログイン必須

Route::get('/map', [MapController::class, 'index'])
    ->name('map.index')
    ->middleware('auth');

Route::post('/map/pin', [MapController::class, 'storePin'])
    ->name('map.pin.store')
    ->middleware('auth');

Route::delete('/map/pin', [MapController::class, 'destroyPin'])
    ->name('map.pin.destroy')
    ->middleware('auth');

// AI Planner (Chat)
Route::get('/ai-planner/{prefectureCode}', [App\Http\Controllers\AiPlannerController::class, 'index'])
    ->name('ai-planner.index')
    ->middleware('auth');

Route::post('/ai-planner', [App\Http\Controllers\AiPlannerController::class, 'store'])
    ->name('ai-planner.store')
    ->middleware('auth');
