<?php   
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\V1\LanguageController;
use App\Http\Controllers\Backend\V1\SystemController;
use App\Http\Controllers\Backend\V1\IntroduceController;
use App\Http\Controllers\Backend\V1\MenuController;
use App\Http\Controllers\Backend\V1\ContactController;

Route::group(['middleware' => ['admin','locale','backend_default_locale']], function () {

    Route::group(['prefix' => 'language'], function () {
        Route::get('index', [LanguageController::class, 'index'])->name('language.index')->middleware(['admin','locale']);
        Route::get('create', [LanguageController::class, 'create'])->name('language.create');
        Route::post('store', [LanguageController::class, 'store'])->name('language.store');
        Route::get('{id}/edit', [LanguageController::class, 'edit'])->where(['id' => '[0-9]+'])->name('language.edit');
        Route::post('{id}/update', [LanguageController::class, 'update'])->where(['id' => '[0-9]+'])->name('language.update');
        Route::get('{id}/delete', [LanguageController::class, 'delete'])->where(['id' => '[0-9]+'])->name('language.delete');
        Route::delete('{id}/destroy', [LanguageController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('language.destroy');
        Route::get('{id}/switch', [LanguageController::class, 'swicthBackendLanguage'])->where(['id' => '[0-9]+'])->name('language.switch');
        Route::get('{id}/{languageId}/{model}/translate', [LanguageController::class, 'translate'])->where(['id' => '[0-9]+', 'languageId' => '[0-9]+'])->name('language.translate');
        Route::post('storeTranslate', [LanguageController::class, 'storeTranslate'])->name('language.storeTranslate');
    });

    Route::group(['prefix' => 'system'], function () {
        Route::get('index', [SystemController::class, 'index'])->name('system.index');
        Route::post('store', [SystemController::class, 'store'])->name('system.store');
        Route::get('{languageId}/translate', [SystemController::class, 'translate'])->where(['languageId' => '[0-9]+'])->name('system.translate');
        Route::post('{languageId}/saveTranslate', [SystemController::class, 'saveTranslate'])->where(['languageId' => '[0-9]+'])->name('system.save.translate');
    });

    Route::group(['prefix' => 'introduce'], function () {
        Route::get('index', [IntroduceController::class, 'index'])->name('introduce.index');
        Route::post('store', [IntroduceController::class, 'store'])->name('introduce.store');
        Route::get('{languageId}/translate', [IntroduceController::class, 'translate'])->where(['languageId' => '[0-9]+'])->name('introduce.translate');
        Route::post('{languageId}/saveTranslate', [IntroduceController::class, 'saveTranslate'])->where(['languageId' => '[0-9]+'])->name('introduce.save.translate');
    });

    Route::group(['prefix' => 'menu'], function () {
        Route::get('index', [MenuController::class, 'index'])->name('menu.index');
        Route::get('create', [MenuController::class, 'create'])->name('menu.create');
        Route::post('store', [MenuController::class, 'store'])->name('menu.store');
        Route::get('{id}/edit', [MenuController::class, 'edit'])->where(['id' => '[0-9]+'])->name('menu.edit');
        Route::get('{id}/editMenu', [MenuController::class, 'editMenu'])->where(['id' => '[0-9]+'])->name('menu.editMenu');
        Route::post('{id}/update', [MenuController::class, 'update'])->where(['id' => '[0-9]+'])->name('menu.update');
        Route::get('{id}/delete', [MenuController::class, 'delete'])->where(['id' => '[0-9]+'])->name('menu.delete');
        Route::delete('{id}/destroy', [MenuController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('menu.destroy');
        Route::get('{id}/children', [MenuController::class, 'children'])->where(['id' => '[0-9]+'])->name('menu.children');
        Route::post('{id}/saveChildren', [MenuController::class, 'saveChildren'])->where(['id' => '[0-9]+'])->name('menu.save.children');
        Route::get('{languageId}/{id}/translate', [MenuController::class, 'translate'])->where(['languageId' => '[0-9]+', 'id' => '[0-9]+'])->name('menu.translate');
        Route::post('{languageId}/saveTranslate', [MenuController::class, 'saveTranslate'])->where(['languageId' => '[0-9]+'])->name('menu.translate.save');
    });

    Route::group(['prefix' => 'contact'], function () {
        Route::get('index', [ContactController::class, 'index'])->name('contact.index');
        Route::get('{id}/delete', [ContactController::class, 'delete'])->where(['id' => '[0-9]+'])->name('contact.delete');
        Route::delete('{id}/destroy', [ContactController::class, 'destroy'])->where(['id' => '[0-9]+'])->name('contact.destroy');
    });
   
    

});

