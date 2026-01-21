<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ProfileController;

Route::get('/register', [RegisterController::class, 'index'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'store']);

Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');


Route::middleware(['auth', 'profile.complete'])->group(function () {

    // 商品購入画面 /purchase/{item_id}
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])
        ->name('purchase.show');

    // 住所変更ページ /purchase/address/{item_id}
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])
        ->name('purchase.address.edit');

    // 購入確定（必要なら）
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])
        ->name('purchase.store');

    // 商品出品画面 /sell
    Route::get('/sell', [SellController::class, 'create'])
        ->name('sell.create');

    Route::post('/sell', [SellController::class, 'store'])
        ->name('sell.store');

});


Route::middleware(['auth'])->group(function () {
    // プロフィール画面（マイページ） /mypage
    Route::get('/mypage', [ProfileController::class, 'mypage'])->name('mypage');
    // プロフィール編集画面 /mypage/profile
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // プロフィール更新（更新後に商品一覧へ飛ばす）
    Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
});