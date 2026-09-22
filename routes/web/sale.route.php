<?php

use App\Http\Controllers\Sale\AuthController;
use App\Http\Controllers\Sale\DashboardController;
use App\Http\Controllers\Sale\PostController;
use App\Http\Controllers\Sale\ProfileController;
use App\Http\Controllers\Sale\ProjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Bang dieu khien cua nhan vien kinh doanh
|--------------------------------------------------------------------------
|
| Tach han khoi /admin: nhan vien kinh doanh chi lam ba viec - sua ho so cua
| minh, sua cac du an duoc giao, va viet bai cho quan tri duyet.
|
| Ba duong dan dang nhap nam NGOAI middleware 'sale', neu khong thi chinh
| trang dang nhap cung bi doi dang nhap - thanh vong lap chuyen huong.
|
*/

Route::group(['prefix' => 'sale'], function () {

    Route::get('dang-nhap', [AuthController::class, 'index'])->name('sale.auth');
    Route::post('dang-nhap', [AuthController::class, 'login'])->name('sale.login');
    Route::get('dang-xuat', [AuthController::class, 'logout'])->name('sale.logout');

    Route::group(['middleware' => ['sale', 'locale', 'backend_default_locale']], function () {

        Route::get('/', [DashboardController::class, 'index'])->name('sale.dashboard');

        Route::get('ho-so', [ProfileController::class, 'index'])->name('sale.profile');
        Route::post('ho-so', [ProfileController::class, 'update'])->name('sale.profile.update');
        Route::post('doi-mat-khau', [ProfileController::class, 'doiMatKhau'])->name('sale.profile.password');

        Route::group(['prefix' => 'du-an'], function () {
            Route::get('/', [ProjectController::class, 'index'])->name('sale.project.index');
            Route::get('them-moi', [ProjectController::class, 'create'])->name('sale.project.create');
            Route::post('them-moi', [ProjectController::class, 'store'])->name('sale.project.store');
            Route::get('{id}/sua', [ProjectController::class, 'edit'])
                ->where(['id' => '[0-9]+'])->name('sale.project.edit');
            Route::post('{id}/sua', [ProjectController::class, 'update'])
                ->where(['id' => '[0-9]+'])->name('sale.project.update');
        });

        Route::group(['prefix' => 'bai-viet'], function () {
            Route::get('/', [PostController::class, 'index'])->name('sale.post.index');
            Route::get('them-moi', [PostController::class, 'create'])->name('sale.post.create');
            Route::post('them-moi', [PostController::class, 'store'])->name('sale.post.store');
            Route::get('{id}/sua', [PostController::class, 'edit'])
                ->where(['id' => '[0-9]+'])->name('sale.post.edit');
            Route::post('{id}/sua', [PostController::class, 'update'])
                ->where(['id' => '[0-9]+'])->name('sale.post.update');
        });
    });
});
