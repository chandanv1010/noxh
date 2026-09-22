<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\V1\DashboardController;
use App\Http\Controllers\Ajax\AttributeController as AjaxAttributeController;
use App\Http\Controllers\Ajax\MenuController as AjaxMenuController;
use App\Http\Controllers\Ajax\ProductController as AjaxProductController;
use App\Http\Controllers\Ajax\PostController as AjaxPostController;
use App\Http\Controllers\Ajax\ExcelController as AjaxExcelController;
use App\Http\Controllers\Ajax\DashboardController as AjaxDashboardController;
use App\Http\Controllers\Ajax\LocationController;
use App\Http\Controllers\Ajax\V2\HandlerController;

Route::group(['middleware' => ['admin', 'locale', 'backend_default_locale']], function () {

    Route::get('dashboard/index', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('ajax/dashboard/changeStatus', [AjaxDashboardController::class, 'changeStatus'])->name('ajax.dashboard.changeStatus');
    Route::post('ajax/dashboard/changeStatusAll', [AjaxDashboardController::class, 'changeStatusAll'])->name('ajax.dashboard.changeStatusAll');
    Route::get('ajax/dashboard/getMenu', [AjaxDashboardController::class, 'getMenu'])->name('ajax.dashboard.getMenu');
    Route::get('ajax/attribute/getAttribute', [AjaxAttributeController::class, 'getAttribute'])->name('ajax.attribute.getAttribute');
    Route::get('ajax/attribute/loadAttribute', [AjaxAttributeController::class, 'loadAttribute'])->name('ajax.attribute.getAttribute');
    Route::post('ajax/menu/createCatalogue', [AjaxMenuController::class, 'createCatalogue'])->name('ajax.menu.createCatalogue');
    Route::post('ajax/menu/drag', [AjaxMenuController::class, 'drag'])->name('ajax.menu.drag');
    Route::post('ajax/menu/deleteMenu', [AjaxMenuController::class, 'deleteMenu'])->name('ajax.menu.deleteMenu');
    Route::get('ajax/product/updateOrder', [AjaxProductController::class, 'updateOrder'])->name('ajax.updateOrder');
    Route::get('ajax/post/updateOrder', [AjaxPostController::class, 'updateOrder'])->name('ajax.updateOrder');
    Route::post('ajax/excel/export', [AjaxExcelController::class, 'export'])->name('ajax.excel.export');
    Route::post('ajax/sort', [HandlerController::class, 'sort'])->name('ajax.sort');
    Route::post('ajax/changeStatusField', [HandlerController::class, 'changeFieldStatus'])->name('ajax.changeFieldStatus');
    Route::get('ajax/dashboard/findModelObject', [AjaxDashboardController::class, 'findModelObject'])->name('ajax.dashboard.findModelObject');
});

Route::group(['middleware' => ['locale']], function () {
    // Chi con mot duong dan ajax: o chon Tinh/Huyen/Xa trong cac form quan tri
    // nap danh sach qua day. Cac duong dan cu (gio hang, so sanh, yeu thich,
    // danh gia, loc san pham) da go cung phan frontend cua truc.
    Route::get('ajax/location/getLocation', [LocationController::class, 'getLocation'])->name('ajax.location.index');
});
