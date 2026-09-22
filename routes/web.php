<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Duong dan cua NOXH.vn
|--------------------------------------------------------------------------
|
| Phan frontend cu cua ban clone truc (gio hang, thanh toan, dang nhap khach,
| so sanh, yeu thich, tim kiem san pham va cac trang di qua RouterController)
| da duoc go bo - website nay khong ban hang truc tuyen.
|
| Chi con hai nhom: cac module quan tri va duong dan ngoai website cua NOXH.
|
*/

// Quan tri
require __DIR__ . '/web/user.route.php';
require __DIR__ . '/web/customer.route.php';
require __DIR__ . '/web/core.route.php';
require __DIR__ . '/web/product.route.php';
require __DIR__ . '/web/post.route.php';
require __DIR__ . '/web/auth.route.php';
require __DIR__ . '/web/ajax.route.php';
require __DIR__ . '/web/noxh.route.php';

// Bang dieu khien rieng cua nhan vien kinh doanh
require __DIR__ . '/web/sale.route.php';

// Tien ich dung chung cho ca hai phia
Route::group(['middleware' => ['locale']], function () {
    Route::get('/sitemap.xml', [App\Http\Controllers\Frontend\SitemapController::class, 'index'])->name('sitemap.xml');
    Route::get('/sitemap', [App\Http\Controllers\Frontend\SitemapController::class, 'index'])->name('sitemap.index');
    Route::get('/thumb', [App\Http\Controllers\ImageResizerController::class, 'resize'])->name('thumb');
});

/*
| Duong dan ngoai website cua NOXH.vn.
|
| Nap CUOI CUNG vi nhom nay co mot route bat cac duong dan mot doan
| (gioi-thieu, chinh-sach-bao-mat, dieu-khoan-su-dung).
*/
require __DIR__ . '/web/noxh-frontend.route.php';
