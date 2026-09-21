<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Noxh\HomeController;
use App\Http\Controllers\Frontend\Noxh\LeadController;
use App\Http\Controllers\Frontend\Noxh\ProjectController;
use App\Http\Controllers\Frontend\Noxh\EligibilityController;
use App\Http\Controllers\Frontend\Noxh\LegalController;
use App\Http\Controllers\Frontend\Noxh\DossierController;
use App\Http\Controllers\Frontend\Noxh\FinanceController;
use App\Http\Controllers\Frontend\Noxh\QaController;
use App\Http\Controllers\Frontend\Noxh\NewsController;

/*
|--------------------------------------------------------------------------
| Duong dan ngoai website cua NOXH.vn
|--------------------------------------------------------------------------
|
| Khai bao TRUOC route bat tat ca {canonical} cua he thong cu, neu khong cac
| duong dan o day se bi route do nuot mat va tra ve 404.
|
| Duong dan khong co duoi .html - dung nhu so do dieu huong da chot.
|
*/

Route::name('noxh.')->group(function () {

    Route::get('/', [HomeController::class, 'index'])->name('home');

    // --- Du an ---------------------------------------------------------------
    Route::get('du-an', [ProjectController::class, 'index'])->name('project.index');
    Route::get('du-an/tinh-thanh', [ProjectController::class, 'provinces'])->name('project.provinces');
    Route::get('du-an/tinh-thanh/{code}', [ProjectController::class, 'byProvince'])->name('project.province');
    Route::get('du-an/{canonical}', [ProjectController::class, 'show'])->name('project.show');

    // --- Kiem tra dieu kien --------------------------------------------------
    Route::get('kiem-tra-dieu-kien', [EligibilityController::class, 'index'])->name('check.index');
    Route::get('kiem-tra-dieu-kien/cau-hoi', [EligibilityController::class, 'form'])->name('check.form');
    Route::post('kiem-tra-dieu-kien/cau-hoi', [EligibilityController::class, 'submit'])->name('check.submit');
    Route::get('kiem-tra-dieu-kien/ket-qua/{code}', [EligibilityController::class, 'result'])->name('check.result');
    Route::post('kiem-tra-dieu-kien/tra-cuu', [EligibilityController::class, 'lookup'])->name('check.lookup');

    // --- Phong phap ly -------------------------------------------------------
    Route::get('phap-ly-noxh', [LegalController::class, 'index'])->name('legal.index');
    Route::get('phap-ly-noxh/van-ban', [LegalController::class, 'documents'])->name('legal.documents');
    Route::get('phap-ly-noxh/van-ban/{id}/tai-ve', [LegalController::class, 'download'])
        ->where(['id' => '[0-9]+'])->name('legal.download');

    // --- Ho so ---------------------------------------------------------------
    Route::get('ho-so', [DossierController::class, 'index'])->name('dossier.index');
    Route::get('ho-so/can-chuan-bi', [DossierController::class, 'index'])->name('dossier.prepare');
    Route::get('ho-so/mau-don', [DossierController::class, 'templates'])->name('dossier.templates');
    Route::get('ho-so/checklist', [DossierController::class, 'checklist'])->name('dossier.checklist');

    // --- Tai chinh -----------------------------------------------------------
    Route::get('tai-chinh', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('tai-chinh/tinh-khoan-vay', [FinanceController::class, 'loan'])->name('finance.loan');
    Route::get('tai-chinh/kha-nang-tai-chinh', [FinanceController::class, 'capacity'])->name('finance.capacity');

    // --- Hoi dap -------------------------------------------------------------
    Route::get('hoi-dap', [QaController::class, 'index'])->name('qa.index');
    Route::get('hoi-dap/{id}', [QaController::class, 'show'])->where(['id' => '[0-9]+'])->name('qa.show');
    Route::post('hoi-dap/gui-cau-hoi', [QaController::class, 'store'])->name('qa.store');

    // --- Tin tuc -------------------------------------------------------------
    Route::get('tin-tuc', [NewsController::class, 'index'])->name('news.index');
    Route::get('tin-tuc/{canonical}', [NewsController::class, 'show'])->name('news.show');

    // --- Form de lai thong tin ------------------------------------------------
    Route::post('de-lai-thong-tin', [LeadController::class, 'store'])->name('lead.store');
});
