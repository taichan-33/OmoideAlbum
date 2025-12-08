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

// 写真へのコメント
Route::post('/photos/{photo}/comments', [App\Http\Controllers\PhotoCommentController::class, 'store'])
    ->name('photo-comments.store')
    ->middleware('auth');

Route::delete('/comments/{comment}', [App\Http\Controllers\PhotoCommentController::class, 'destroy'])
    ->name('photo-comments.destroy')
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

Route::patch('/suggestions/{suggestion}/toggle-status', [SuggestionController::class, 'toggleStatus'])
    ->name('suggestions.toggleStatus')
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

// Stats
Route::get('/stats', [App\Http\Controllers\StatsController::class, 'index'])
    ->name('stats.index')
    ->middleware('auth');

// Packing Items
Route::post('/trips/{trip}/packing-items', [App\Http\Controllers\PackingItemController::class, 'store'])
    ->name('packing-items.store')
    ->middleware('auth');

Route::post('/trips/{trip}/packing-items/batch', [App\Http\Controllers\PackingItemController::class, 'storeBatch'])
    ->name('packing-items.store-batch')
    ->middleware('auth');

Route::put('/packing-items/{item}', [App\Http\Controllers\PackingItemController::class, 'update'])
    ->name('packing-items.update')
    ->middleware('auth');

Route::delete('/packing-items/{item}', [App\Http\Controllers\PackingItemController::class, 'destroy'])
    ->name('packing-items.destroy')
    ->middleware('auth');

// Timeline
Route::get('/timeline', [App\Http\Controllers\TimelineController::class, 'index'])
    ->name('timeline.index')
    ->middleware('auth');

Route::post('/timeline', [App\Http\Controllers\TimelineController::class, 'store'])
    ->name('timeline.store')
    ->middleware('auth');

Route::get('/api/timeline/attachables', [App\Http\Controllers\TimelineController::class, 'getAttachables'])
    ->name('timeline.attachables')
    ->middleware('auth');

Route::post('/timeline/{post}/reaction', [App\Http\Controllers\TimelineController::class, 'toggleReaction'])
    ->name('timeline.reaction')
    ->middleware('auth');

Route::get('/timeline/{post}', [App\Http\Controllers\TimelineController::class, 'show'])
    ->name('timeline.show')
    ->middleware('auth');

// Notifications
Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])
    ->name('notifications.index')
    ->middleware('auth');

Route::post('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'readAll'])
    ->name('notifications.readAll')
    ->middleware('auth');

Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'read'])
    ->name('notifications.read')
    ->middleware('auth');
