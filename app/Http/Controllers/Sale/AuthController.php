<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Dang nhap rieng cho nhan vien kinh doanh.
 *
 * Dung chung bang users va chung guard voi trang quan tri - chi khac trang
 * dang nhap va noi di den sau khi vao. Khong tach guard rieng vi nhu vay se
 * phai nhan doi ca cau hinh phien lan bang mat khau ma khong duoc gi them.
 */
class AuthController extends Controller
{
    public function index()
    {
        if (Auth::id() > 0 && Auth::user()->laNhanVienKinhDoanh()) {
            return redirect()->route('sale.dashboard');
        }

        return view('sale.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Bạn chưa nhập email',
            'email.email' => 'Email không đúng định dạng',
            'password.required' => 'Bạn chưa nhập mật khẩu',
        ]);

        $thongTin = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        if (!Auth::attempt($thongTin)) {
            return redirect()->route('sale.auth')
                ->with('error', 'Email hoặc mật khẩu không chính xác');
        }

        // Dang nhap dung nhung khong phai nhan vien kinh doanh thi dang xuat
        // ngay. De nguyen phien thi ho se bi middleware day sang /admin roi lai
        // bi Gate chan - roi ma khong hieu vi sao.
        if (!Auth::user()->laNhanVienKinhDoanh()) {
            Auth::logout();
            return redirect()->route('sale.auth')
                ->with('error', 'Tài khoản này không phải nhân viên kinh doanh. Vui lòng đăng nhập ở trang quản trị.');
        }

        if ((int) Auth::user()->publish !== 2) {
            Auth::logout();
            return redirect()->route('sale.auth')->with('error', 'Tài khoản của bạn đang bị khóa');
        }

        $request->session()->regenerate();

        return redirect()->route('sale.dashboard')->with('success', 'Đăng nhập thành công');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sale.auth');
    }
}
