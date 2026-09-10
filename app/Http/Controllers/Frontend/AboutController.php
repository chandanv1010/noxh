<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\FrontendController;
use Illuminate\Http\Request;
use App\Services\V1\Core\WidgetService;
use App\Services\V1\Core\SlideService;

class AboutController extends FrontendController
{
    protected $language;
    protected $system;
    protected $widgetService;
    protected $slideService;

    public function __construct(
        WidgetService $widgetService,
        SlideService $slideService
    ) {
        $this->widgetService = $widgetService;
        $this->slideService = $slideService;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $widgets = $this->widgetService->getWidget([
            ['keyword' => 'homepage-news', 'object' => true],
            ['keyword' => 'featured-project', 'object' => true],
            ['keyword' => 'feedback', 'object' => true],
            ['keyword' => 'about-us', 'object' => true],
        ], $this->language);

        $config = $this->config();
        $system = $this->system;
        
        $seo = [
            'meta_title' => 'Về Chúng Tôi',
            'meta_description' => 'Tìm hiểu thêm về Tazen - thương hiệu thiết bị vệ sinh cao cấp hàng đầu Việt Nam.',
            'meta_keyword' => 'tazen, gioi thieu tazen, thiet bi ve sinh, lavabo, sen tam',
            'meta_image' => '',
            'canonical' => write_url('gioi-thieu')
        ];

        $template = 'frontend.about.index';

        $slides = $this->slideService->getSlide(
            ['main-slide'],
            $this->language
        );

        return view($template, compact(
            'widgets',
            'config',
            'seo',
            'system',
            'slides'
        ));
    }

    private function config()
    {
        return [
            'language' => $this->language,
            'css' => [],
            'js' => []
        ];
    }
}
