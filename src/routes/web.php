<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

use App\Http\Controllers\ItemController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SellController;
use App\Http\Controllers\ProfileController;

Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', '認証メールを再送しました。受信ボックスを確認してください。');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()
            ->route('profile.edit')
            ->with('message', 'メール認証が完了しました！プロフィールを設定してください。');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/item/{item_id}/like', [ItemController::class, 'toggleLike'])->name('items.like');
    Route::post('/item/{item_id}/comment', [CommentController::class, 'store'])->name('comments.store');

    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('verified')->group(function () {
        Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    Route::middleware(['verified', 'profile.complete'])->group(function () {

        Route::get('/mypage', [ProfileController::class, 'mypage'])->name('mypage');

        Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');

        Route::get('/purchase/{item_id}', [PurchaseController::class, 'index'])->name('purchase.show');
        Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.address.edit');
        Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.address.update');
        Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');

        Route::get('/sell', [SellController::class, 'create'])->name('sell.create');
        Route::post('/sell', [SellController::class, 'store'])->name('sell.store');
    });
});