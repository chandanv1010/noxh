<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Repositories\Core\LanguageRepository;

class LanguageComposerServiceProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('backend.dashboard.layout', function ($view) {
            $langugeRepository = $this->app->make(LanguageRepository::class);
            $languages = $langugeRepository->all();
            $view->with('languages', $languages);
        });

        View::composer('frontend.component.hero_section', function ($view) {
            $slideService = $this->app->make(\App\Services\V1\Core\SlideService::class);
            $slides = $slideService->getSlide(
                [\App\Enums\SlideEnum::MAIN, \App\Enums\SlideEnum::MOBILE, \App\Enums\SlideEnum::TECHSTAFF, \App\Enums\SlideEnum::PARTNER, 'commit', 'commit-2', 'banner-home'],
                1
            );
            $view->with('slides', $slides);
        });

        /**
         * Anh banner doc cho dien thoai, lay tu slide nhom 'mobile-slide'.
         *
         * Dat o composer toan cuc vi NoxhComposer da chay cho moi view cua
         * website NOXH, va trang chu can no ngay o khoi banner. Slide nam trong
         * bang slides (cot keyword = 'mobile-slide'), moi ban ghi mot anh - co
         * the la nhieu anh mai ve sau, khac voi o hero_image_mobile trong Cau
         * hinh -> Gioi thieu von chi giu duoc MOT anh.
         */
        View::composer('frontend.*', function ($view) {
            $slideService = $this->app->make(\App\Services\V1\Core\SlideService::class);
            $slide = $slideService->getSlide([\App\Enums\SlideEnum::MOBILE], 1);

            $view->with('slideBannerMobile', $slide[\App\Enums\SlideEnum::MOBILE] ?? null);
        });
    }
}
