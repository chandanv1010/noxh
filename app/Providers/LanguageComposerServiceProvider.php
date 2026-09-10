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
                [\App\Enums\SlideEnum::MAIN, \App\Enums\SlideEnum::TECHSTAFF, \App\Enums\SlideEnum::PARTNER, 'commit', 'commit-2', 'banner-home'],
                1
            );
            $view->with('slides', $slides);
        });
    }
}
