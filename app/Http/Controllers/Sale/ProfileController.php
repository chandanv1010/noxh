<?php

namespace App\Http\Controllers\Sale;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Nhan vien tu sua ho so cua minh. Day cung chinh la thong tin hien ra ngoai
 * website o khoi "Nhan vien kinh doanh phu trach" cua tung du an.
 */
class ProfileController extends SaleController
{
    public function index()
    {
        return $this->khung('sale.profile.index', [
            'nguoiDung' => Auth::user(),
            'tieuDe' => 'Hồ sơ của tôi',
        ]);
    }

    public function update(Request $request)
    {
        $toi = Auth::user();

        $duLieu = $request->validate([
            'name' => 'required|string|max:191',
            'title' => 'nullable|string|max:150',
            'phone' => 'nullable|string|max:30',
            'zalo' => 'nullable|string|max:50',
            'public_email' => 'nullable|email|max:191',
            'address' => 'nullable|string|max:500',
            'birthday' => 'nullable|date',
            'image' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($toi->id)],
        ], [
            'name.required' => 'Bạn chưa nhập họ tên',
            'email.required' => 'Bạn chưa nhập email đăng nhập',
            'email.unique' => 'Email này đã có người dùng',
            'public_email.email' => 'Email hiển thị không đúng định dạng',
        ]);

        // Nhan vien KHONG duoc tu doi nhom thanh vien hay trang thai tai khoan:
        // hai cot do quyet dinh quyen han, chi quan tri moi duoc dong vao. Vi
        // the o day liet ke tay tung cot thay vi $request->all().
        User::where('id', $toi->id)->update($duLieu);

        return redirect()->route('sale.profile')->with('success', 'Cập nhật hồ sơ thành công');
    }

    public function doiMatKhau(Request $request)
    {
        $request->validate([
            'mat_khau_cu' => 'required',
            'mat_khau_moi' => 'required|min:6|confirmed',
        ], [
            'mat_khau_cu.required' => 'Bạn chưa nhập mật khẩu hiện tại',
            'mat_khau_moi.required' => 'Bạn chưa nhập mật khẩu mới',
            'mat_khau_moi.min' => 'Mật khẩu mới phải từ 6 ký tự',
            'mat_khau_moi.confirmed' => 'Hai lần nhập mật khẩu mới không khớp nhau',
        ]);

        if (!Hash::check($request->input('mat_khau_cu'), Auth::user()->password)) {
            return redirect()->route('sale.profile')->with('error', 'Mật khẩu hiện tại không đúng');
        }

        User::where('id', Auth::id())->update([
            'password' => Hash::make($request->input('mat_khau_moi')),
        ]);

        return redirect()->route('sale.profile')->with('success', 'Đổi mật khẩu thành công');
    }
}
