<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Router;
use App\Models\Product;
use App\Models\ProductCatalogue;
use App\Models\Post;
use App\Models\PostCatalogue;
use App\Models\Language;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale() ?? 'vn';
        $language = Language::where('canonical', $locale)->first();
        $languageId = $language ? $language->id : 1;

        $urls = [];

        // 1. Trang chủ
        $urls[] = [
            'loc' => url('/'),
            'lastmod' => date('c'),
            'changefreq' => 'daily',
            'priority' => '1.0'
        ];

        // 2. Trang Liên hệ
        $urls[] = [
            'loc' => write_url('lien-he'),
            'lastmod' => date('c'),
            'changefreq' => 'weekly',
            'priority' => '0.8'
        ];

        // 3. Tuyến đường động từ cơ sở dữ liệu (routers)
        $routers = Router::where('language_id', $languageId)->get();

        foreach ($routers as $router) {
            $publish = true;
            $priority = '0.8';
            $changefreq = 'weekly';

            if (strpos($router->controllers, 'ProductCatalogueController') !== false) {
                $model = ProductCatalogue::find($router->module_id);
                if (!$model || $model->publish != 2) {
                    $publish = false;
                }
                $priority = '0.8';
                $changefreq = 'daily';
            } elseif (strpos($router->controllers, 'ProductController') !== false) {
                $model = Product::find($router->module_id);
                if (!$model || $model->publish != 2) {
                    $publish = false;
                }
                $priority = '0.9';
                $changefreq = 'weekly';
            } elseif (strpos($router->controllers, 'PostCatalogueController') !== false) {
                $model = PostCatalogue::find($router->module_id);
                if (!$model || $model->publish != 2) {
                    $publish = false;
                }
                $priority = '0.7';
                $changefreq = 'weekly';
            } elseif (strpos($router->controllers, 'PostController') !== false) {
                $model = Post::find($router->module_id);
                if (!$model || $model->publish != 2) {
                    $publish = false;
                }
                $priority = '0.7';
                $changefreq = 'weekly';
            }

            if ($publish) {
                $urls[] = [
                    'loc' => write_url($router->canonical),
                    'lastmod' => $router->updated_at ? $router->updated_at->toAtomString() : date('c'),
                    'changefreq' => $changefreq,
                    'priority' => $priority
                ];
            }
        }

        return response()
            ->view('frontend.sitemap.index', compact('urls'))
            ->header('Content-Type', 'text/xml; charset=UTF-8');
    }
}
