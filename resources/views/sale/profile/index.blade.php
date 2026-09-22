<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('sale.profile.update') }}" method="post">
            @csrf
            <div class="ibox">
                <div class="ibox-title"><h5>Thông tin hiển thị ngoài website</h5></div>
                <div class="ibox-content">
                    <p class="text-muted" style="font-size:13px">
                        Những ô dưới đây là thứ khách nhìn thấy ở mục
                        <strong>Nhân viên kinh doanh phụ trách</strong> trên trang từng dự án.
                    </p>

                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label">Họ và tên <span class="text-danger">(*)</span></label>
                                <input type="text" name="name" value="{{ old('name', $nguoiDung->name) }}"
                                       class="form-control" required>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label">Chức danh</label>
                                <input type="text" name="title" value="{{ old('title', $nguoiDung->title) }}"
                                       class="form-control" placeholder="Ví dụ: Chuyên viên tư vấn">
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label">Điện thoại</label>
                                <input type="text" name="phone" value="{{ old('phone', $nguoiDung->phone) }}"
                                       class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label">Zalo</label>
                                <input type="text" name="zalo" value="{{ old('zalo', $nguoiDung->zalo) }}"
                                       class="form-control" placeholder="Để trống nếu trùng số điện thoại">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label">Email hiển thị</label>
                                <input type="email" name="public_email"
                                       value="{{ old('public_email', $nguoiDung->public_email) }}"
                                       class="form-control" placeholder="Khác với email đăng nhập">
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-8">
                            <div class="form-row">
                                <label class="control-label">Ảnh chân dung</label>
                                <input type="text" name="image" value="{{ old('image', $nguoiDung->image) }}"
                                       class="form-control" placeholder="Đường dẫn ảnh">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label">Ngày sinh</label>
                                <input type="date" name="birthday"
                                       value="{{ old('birthday', $nguoiDung->birthday) }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    @if($nguoiDung->image)
                        <img src="{{ $nguoiDung->image }}" alt="{{ $nguoiDung->name }}"
                             style="width:110px;height:110px;object-fit:cover;border-radius:8px;margin-bottom:15px">
                    @endif

                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label">Địa chỉ</label>
                                <input type="text" name="address" value="{{ old('address', $nguoiDung->address) }}"
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label">Giới thiệu ngắn</label>
                                <textarea name="description" class="form-control" rows="4"
                                          placeholder="Vài dòng về kinh nghiệm, khu vực phụ trách...">{{ old('description', $nguoiDung->description) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ibox">
                <div class="ibox-title"><h5>Tài khoản đăng nhập</h5></div>
                <div class="ibox-content">
                    <div class="form-row">
                        <label class="control-label">Email đăng nhập <span class="text-danger">(*)</span></label>
                        <input type="email" name="email" value="{{ old('email', $nguoiDung->email) }}"
                               class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="text-right mb15">
                <button class="btn btn-primary" type="submit">Lưu hồ sơ</button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <form action="{{ route('sale.profile.password') }}" method="post">
            @csrf
            <div class="ibox">
                <div class="ibox-title"><h5>Đổi mật khẩu</h5></div>
                <div class="ibox-content">
                    <div class="form-row mb15">
                        <label class="control-label">Mật khẩu hiện tại</label>
                        <input type="password" name="mat_khau_cu" class="form-control" required>
                    </div>
                    <div class="form-row mb15">
                        <label class="control-label">Mật khẩu mới</label>
                        <input type="password" name="mat_khau_moi" class="form-control" required>
                    </div>
                    <div class="form-row mb15">
                        <label class="control-label">Nhập lại mật khẩu mới</label>
                        <input type="password" name="mat_khau_moi_confirmation" class="form-control" required>
                    </div>
                    <button class="btn btn-white" type="submit">Đổi mật khẩu</button>
                </div>
            </div>
        </form>

        <div class="ibox">
            <div class="ibox-title"><h5>Nhóm thành viên</h5></div>
            <div class="ibox-content">
                <p style="margin:0">{{ $nguoiDung->user_catalogues->name ?? '—' }}</p>
                <p class="text-muted" style="font-size:12px;margin:6px 0 0">
                    Chỉ quản trị viên đổi được nhóm và trạng thái tài khoản.
                </p>
            </div>
        </div>
    </div>
</div>
