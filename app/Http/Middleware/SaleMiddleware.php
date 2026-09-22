<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Canh cua cua bang dieu khien /sale.
 *
 * Ba lop kiem tra, thieu mot lop la lot:
 *   1. Da dang nhap chua.
 *   2. Tai khoan con hieu luc khong (publish == 2).
 *   3. Co thuoc nhom duoc danh dau la nhan vien kinh doanh khong.
 *
 * Quan tri vien KHONG vao duoc /sale: ho da co /admin voi day du quyen, cho
 * ho vao ca hai noi chi lam ro ranh gioi mo di. Thay vi bao loi cut lui thi
 * day ho ve dashboard quen thuoc.
 */
class SaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::id() === null) {
            return redirect()->route('sale.auth')
                ->with('error', 'Bạn phải đăng nhập để sử dụng chức năng này');
        }

        $nguoiDung = Auth::user();

        if ((int) $nguoiDung->publish !== 2) {
            Auth::logout();
            return redirect()->route('sale.auth')
                ->with('error', 'Tài khoản của bạn đang bị khóa');
        }

        if (!$nguoiDung->laNhanVienKinhDoanh()) {
            return redirect()->route('dashboard.index');
        }

        return $next($request);
    }
}
