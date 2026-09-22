<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Support\Facades\Auth;

/**
 * Phan dung chung cua moi man hinh trong bang dieu khien /sale.
 *
 * Giu hai thu can o gan nhu moi truy van: id ngon ngu dang dung va id nguoi
 * dang nhap.
 */
abstract class SaleController extends Controller
{
    protected $language;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $ngonNgu = Language::where('canonical', app()->getLocale())->first();
            $this->language = $ngonNgu->id;
            return $next($request);
        });
    }

    protected function toiLaAi(): int
    {
        return (int) Auth::id();
    }

    /**
     * Khung view chung. Moi man hinh chi viec dua ten template vao.
     */
    protected function khung(string $template, array $duLieu = [])
    {
        return view('sale.layout', array_merge($duLieu, ['template' => $template]));
    }
}
