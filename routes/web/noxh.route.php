<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\V1\Noxh\InvestorController;
use App\Http\Controllers\Backend\V1\Noxh\LegalDocumentController;
use App\Http\Controllers\Backend\V1\Noxh\ExpertController;
use App\Http\Controllers\Backend\V1\Noxh\LoanPackageController;
use App\Http\Controllers\Backend\V1\Noxh\DossierSetController;
use App\Http\Controllers\Backend\V1\Noxh\DossierItemController;
use App\Http\Controllers\Backend\V1\Noxh\EligibilityQuestionController;
use App\Http\Controllers\Backend\V1\Noxh\EligibilityOptionController;
use App\Http\Controllers\Backend\V1\Noxh\EligibilityCheckController;
use App\Http\Controllers\Backend\V1\Noxh\QaQuestionController;
use App\Http\Controllers\Backend\V1\Noxh\ProjectMilestoneController;
use App\Http\Controllers\Backend\V1\Noxh\ProjectDocumentController;
use App\Http\Controllers\Backend\V1\Noxh\ProjectFaqController;

/*
|--------------------------------------------------------------------------
| Duong dan quan tri cua cac module rieng cua NOXH
|--------------------------------------------------------------------------
|
| Tach ra file rieng cho de theo doi: day la phan viet moi cho du an nha o
| xa hoi, khong phai phan ke thua tu ban clone ban dau.
|
| Luu y thu tu: nhom co duong dan dai hon (vi du dossier/item) phai khai bao
| TRUOC nhom ngan hon cung tien to, neu khong nhom ngan se bat mat.
|
*/
Route::group(['middleware' => ['admin', 'locale', 'backend_default_locale']], function () {

    Route::group(['prefix' => 'investor'], function () {
        Route::get('index', [InvestorController::class, 'index'])->name('investor.index');
        Route::get('create', [InvestorController::class, 'create'])->name('investor.create');
        Route::post('store', [InvestorController::class, 'store'])->name('investor.store');
        Route::get('{id}/edit', [InvestorController::class, 'edit'])->where(['id' => '[0-9]+'])->name('investor.edit');
        Route::post('{id}/update', [InvestorController::class, 'update'])->where(['id' => '[0-9]+'])->name('investor.update');
        Route::get('{id}/delete', [InvestorController::class, 'delete'])->where(['id' => '[0-9]+'])->name('investor.delete');
        Route::delete('{id}/destroy', [InvestorController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('investor.destroy');
    });

    Route::group(['prefix' => 'legal-document'], function () {
        Route::get('index', [LegalDocumentController::class, 'index'])->name('legal.document.index');
        Route::get('create', [LegalDocumentController::class, 'create'])->name('legal.document.create');
        Route::post('store', [LegalDocumentController::class, 'store'])->name('legal.document.store');
        Route::get('{id}/edit', [LegalDocumentController::class, 'edit'])->where(['id' => '[0-9]+'])->name('legal.document.edit');
        Route::post('{id}/update', [LegalDocumentController::class, 'update'])->where(['id' => '[0-9]+'])->name('legal.document.update');
        Route::get('{id}/delete', [LegalDocumentController::class, 'delete'])->where(['id' => '[0-9]+'])->name('legal.document.delete');
        Route::delete('{id}/destroy', [LegalDocumentController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('legal.document.destroy');
    });

    Route::group(['prefix' => 'expert'], function () {
        Route::get('index', [ExpertController::class, 'index'])->name('expert.index');
        Route::get('create', [ExpertController::class, 'create'])->name('expert.create');
        Route::post('store', [ExpertController::class, 'store'])->name('expert.store');
        Route::get('{id}/edit', [ExpertController::class, 'edit'])->where(['id' => '[0-9]+'])->name('expert.edit');
        Route::post('{id}/update', [ExpertController::class, 'update'])->where(['id' => '[0-9]+'])->name('expert.update');
        Route::get('{id}/delete', [ExpertController::class, 'delete'])->where(['id' => '[0-9]+'])->name('expert.delete');
        Route::delete('{id}/destroy', [ExpertController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('expert.destroy');
    });

    Route::group(['prefix' => 'loan-package'], function () {
        Route::get('index', [LoanPackageController::class, 'index'])->name('loan.package.index');
        Route::get('create', [LoanPackageController::class, 'create'])->name('loan.package.create');
        Route::post('store', [LoanPackageController::class, 'store'])->name('loan.package.store');
        Route::get('{id}/edit', [LoanPackageController::class, 'edit'])->where(['id' => '[0-9]+'])->name('loan.package.edit');
        Route::post('{id}/update', [LoanPackageController::class, 'update'])->where(['id' => '[0-9]+'])->name('loan.package.update');
        Route::get('{id}/delete', [LoanPackageController::class, 'delete'])->where(['id' => '[0-9]+'])->name('loan.package.delete');
        Route::delete('{id}/destroy', [LoanPackageController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('loan.package.destroy');
    });

    Route::group(['prefix' => 'dossier/set'], function () {
        Route::get('index', [DossierSetController::class, 'index'])->name('dossier.set.index');
        Route::get('create', [DossierSetController::class, 'create'])->name('dossier.set.create');
        Route::post('store', [DossierSetController::class, 'store'])->name('dossier.set.store');
        Route::get('{id}/edit', [DossierSetController::class, 'edit'])->where(['id' => '[0-9]+'])->name('dossier.set.edit');
        Route::post('{id}/update', [DossierSetController::class, 'update'])->where(['id' => '[0-9]+'])->name('dossier.set.update');
        Route::get('{id}/delete', [DossierSetController::class, 'delete'])->where(['id' => '[0-9]+'])->name('dossier.set.delete');
        Route::delete('{id}/destroy', [DossierSetController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('dossier.set.destroy');
    });

    Route::group(['prefix' => 'dossier/item'], function () {
        Route::get('index', [DossierItemController::class, 'index'])->name('dossier.item.index');
        Route::get('create', [DossierItemController::class, 'create'])->name('dossier.item.create');
        Route::post('store', [DossierItemController::class, 'store'])->name('dossier.item.store');
        Route::get('{id}/edit', [DossierItemController::class, 'edit'])->where(['id' => '[0-9]+'])->name('dossier.item.edit');
        Route::post('{id}/update', [DossierItemController::class, 'update'])->where(['id' => '[0-9]+'])->name('dossier.item.update');
        Route::get('{id}/delete', [DossierItemController::class, 'delete'])->where(['id' => '[0-9]+'])->name('dossier.item.delete');
        Route::delete('{id}/destroy', [DossierItemController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('dossier.item.destroy');
    });

    Route::group(['prefix' => 'eligibility/question'], function () {
        Route::get('index', [EligibilityQuestionController::class, 'index'])->name('eligibility.question.index');
        Route::get('create', [EligibilityQuestionController::class, 'create'])->name('eligibility.question.create');
        Route::post('store', [EligibilityQuestionController::class, 'store'])->name('eligibility.question.store');
        Route::get('{id}/edit', [EligibilityQuestionController::class, 'edit'])->where(['id' => '[0-9]+'])->name('eligibility.question.edit');
        Route::post('{id}/update', [EligibilityQuestionController::class, 'update'])->where(['id' => '[0-9]+'])->name('eligibility.question.update');
        Route::get('{id}/delete', [EligibilityQuestionController::class, 'delete'])->where(['id' => '[0-9]+'])->name('eligibility.question.delete');
        Route::delete('{id}/destroy', [EligibilityQuestionController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('eligibility.question.destroy');
    });

    Route::group(['prefix' => 'eligibility/option'], function () {
        Route::get('index', [EligibilityOptionController::class, 'index'])->name('eligibility.option.index');
        Route::get('create', [EligibilityOptionController::class, 'create'])->name('eligibility.option.create');
        Route::post('store', [EligibilityOptionController::class, 'store'])->name('eligibility.option.store');
        Route::get('{id}/edit', [EligibilityOptionController::class, 'edit'])->where(['id' => '[0-9]+'])->name('eligibility.option.edit');
        Route::post('{id}/update', [EligibilityOptionController::class, 'update'])->where(['id' => '[0-9]+'])->name('eligibility.option.update');
        Route::get('{id}/delete', [EligibilityOptionController::class, 'delete'])->where(['id' => '[0-9]+'])->name('eligibility.option.delete');
        Route::delete('{id}/destroy', [EligibilityOptionController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('eligibility.option.destroy');
    });

    // Ket qua kiem tra dieu kien: chi doc va xoa, khong co them/sua.
    Route::group(['prefix' => 'eligibility/check'], function () {
        Route::get('index', [EligibilityCheckController::class, 'index'])->name('eligibility.check.index');
        Route::get('{id}/edit', [EligibilityCheckController::class, 'edit'])->where(['id' => '[0-9]+'])->name('eligibility.check.edit');
        Route::get('{id}/delete', [EligibilityCheckController::class, 'delete'])->where(['id' => '[0-9]+'])->name('eligibility.check.delete');
        Route::delete('{id}/destroy', [EligibilityCheckController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('eligibility.check.destroy');
    });

    // Hoi dap: cau hoi chi sinh ra tu website nen khong co man hinh them moi.
    Route::group(['prefix' => 'qa/question'], function () {
        Route::get('index', [QaQuestionController::class, 'index'])->name('qa.question.index');
        Route::get('{id}/edit', [QaQuestionController::class, 'edit'])->where(['id' => '[0-9]+'])->name('qa.question.edit');
        Route::post('{id}/update', [QaQuestionController::class, 'update'])->where(['id' => '[0-9]+'])->name('qa.question.update');
        Route::get('{id}/delete', [QaQuestionController::class, 'delete'])->where(['id' => '[0-9]+'])->name('qa.question.delete');
        Route::delete('{id}/destroy', [QaQuestionController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('qa.question.destroy');
    });

    Route::group(['prefix' => 'project/milestone'], function () {
        Route::get('index', [ProjectMilestoneController::class, 'index'])->name('project.milestone.index');
        Route::get('create', [ProjectMilestoneController::class, 'create'])->name('project.milestone.create');
        Route::post('store', [ProjectMilestoneController::class, 'store'])->name('project.milestone.store');
        Route::get('{id}/edit', [ProjectMilestoneController::class, 'edit'])->where(['id' => '[0-9]+'])->name('project.milestone.edit');
        Route::post('{id}/update', [ProjectMilestoneController::class, 'update'])->where(['id' => '[0-9]+'])->name('project.milestone.update');
        Route::get('{id}/delete', [ProjectMilestoneController::class, 'delete'])->where(['id' => '[0-9]+'])->name('project.milestone.delete');
        Route::delete('{id}/destroy', [ProjectMilestoneController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('project.milestone.destroy');
    });
    Route::group(['prefix' => 'project/document'], function () {
        Route::get('index', [ProjectDocumentController::class, 'index'])->name('project.document.index');
        Route::get('create', [ProjectDocumentController::class, 'create'])->name('project.document.create');
        Route::post('store', [ProjectDocumentController::class, 'store'])->name('project.document.store');
        Route::get('{id}/edit', [ProjectDocumentController::class, 'edit'])->where(['id' => '[0-9]+'])->name('project.document.edit');
        Route::post('{id}/update', [ProjectDocumentController::class, 'update'])->where(['id' => '[0-9]+'])->name('project.document.update');
        Route::get('{id}/delete', [ProjectDocumentController::class, 'delete'])->where(['id' => '[0-9]+'])->name('project.document.delete');
        Route::delete('{id}/destroy', [ProjectDocumentController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('project.document.destroy');
    });
    Route::group(['prefix' => 'project/faq'], function () {
        Route::get('index', [ProjectFaqController::class, 'index'])->name('project.faq.index');
        Route::get('create', [ProjectFaqController::class, 'create'])->name('project.faq.create');
        Route::post('store', [ProjectFaqController::class, 'store'])->name('project.faq.store');
        Route::get('{id}/edit', [ProjectFaqController::class, 'edit'])->where(['id' => '[0-9]+'])->name('project.faq.edit');
        Route::post('{id}/update', [ProjectFaqController::class, 'update'])->where(['id' => '[0-9]+'])->name('project.faq.update');
        Route::get('{id}/delete', [ProjectFaqController::class, 'delete'])->where(['id' => '[0-9]+'])->name('project.faq.delete');
        Route::delete('{id}/destroy', [ProjectFaqController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('project.faq.destroy');
    });

});
