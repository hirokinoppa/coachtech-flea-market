<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Public（誰でもOK）
|--------------------------------------------------------------------------
*/
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');

/*
|--------------------------------------------------------------------------
| Guest（未ログインだけ）
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Auth（ログイン必須）
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ログアウト
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // プロフィール編集は「未完了でも入れる」必要があるので auth のみ
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');

    // それ以外は「プロフィール完了必須」
    Route::middleware('profile.complete')->group(function () {

        // マイページ
        Route::get('/mypage', [ProfileController::class, 'mypage'])->name('mypage');

        // 商品購入画面 /purchase/{item_id}
        Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])
            ->name('purchase.show');

        // 住所変更ページ /purchase/address/{item_id}
        Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])
            ->name('purchase.address.edit');

        // 購入確定
        Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])
            ->name('purchase.store');

        // 商品出品画面 /sell
        Route::get('/sell', [SellController::class, 'create'])
            ->name('sell.create');
        Route::post('/sell', [SellController::class, 'store'])
            ->name('sell.store');
    });
});