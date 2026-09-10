<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;

use App\Repositories\Core\SystemRepository;
use App\Services\V1\Core\SlideService;
use App\Enums\SlideEnum;
use App\Services\V1\Core\WidgetService;
use Illuminate\Http\Request;

class HomeController extends FrontendController
{
    protected $systemRepository;
    protected $slideService;
    protected $widgetService;
    protected $scholarService;

    public function __construct(
        SlideService $slideService,
        SystemRepository $systemRepository,
        WidgetService $widgetService,
    ) {
        $this->slideService = $slideService;
        $this->systemRepository = $systemRepository;
        $this->widgetService = $widgetService;
        
        parent::__construct(
            $systemRepository,
        );
    }


    public function index()
    {
        $config = $this->config();

        $slides = $this->slideService->getSlide(
            [SlideEnum::MAIN, SlideEnum::TECHSTAFF, SlideEnum::PARTNER, 'commit', 'commit-2', 'banner-home'],
            $this->language
        );

        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'commit'],
            ['keyword' => 'about-us'],
            ['keyword' => 'solution'],
            [
                'keyword' => 'solution-product',
                'children' => true
            ],
            ['keyword' => 'featured-project'],
            ['keyword' => 'homepage-news'],
            ['keyword' => 'homepage-video'],
            [
                'keyword' => 'homepage-categories',
                'limit' => 8
            ],
        ], $this->language);


        $system = $this->system;
        $seo = [
            'meta_title' => $this->system['seo_meta_title'],
            'meta_keyword' => $this->system['seo_meta_keyword'],
            'meta_description' => $this->system['seo_meta_description'],
            'meta_image' => $this->system['seo_meta_images'],
            'canonical' => config('app.url'),
        ];
        $schema = $this->schema($seo);
        $template = 'frontend.homepage.home.index';
        return view($template, compact(
            'config',
            'slides',
            'seo',
            'system',
            'schema',
            'widgets',
        ));
    }

    /**
         * @param array $seo
         * @return string
         */
        public function schema(array $seo = []): string
        {
            $schema = "<script type='application/ld+json'>
                {
                    \"@context\": \"https://schema.org\",
                    \"@type\": \"WebSite\",
                    \"name\": \"" . ($seo['meta_title'] ?? '') . "\",
                    \"url\": \"" . ($seo['canonical'] ?? '') . "\",
                    \"description\": \"" . ($seo['meta_description'] ?? '') . "\",
                    \"publisher\": {
                        \"@type\": \"Organization\",
                        \"name\": \"" . ($seo['meta_title'] ?? '') . "\"
                    },
                    \"potentialAction\": {
                        \"@type\": \"SearchAction\",
                        \"target\": {
                            \"@type\": \"EntryPoint\",
                            \"urlTemplate\": \"" . ($seo['canonical'] ?? '') . "search?q={search_term_string}\"
                        },
                        \"query-input\": \"required name=search_term_string\"
                    }
                }
            </script>";

            return $schema;
        }

    private function config()
    {
        return [
            'language' => $this->language,
            'css' => [
                '__frontend/resources/style.css'
            ],
            'js' => []
        ];
    }



}