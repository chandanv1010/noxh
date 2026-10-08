{{--
    Form "Điền form để nhận hồ sơ" — đặt ở CUỐI trang /ho-so.

    Trước đây chỗ này là thẻ chuyên gia (component expert-box) nằm trong cột phụ.
    Trên điện thoại cột phụ được đẩy lên TRƯỚC phần nội dung, nên thẻ đó hiện ra
    lửng lơ giữa trang, cắt ngang danh sách hồ sơ đang đọc. Nay thay bằng form này
    và cho xuống cuối trang.

    Tham số:
      $tatCaBo  danh sách nhóm đối tượng (DossierSet) để đổ vào ô chọn
      $boChon   nhóm đang lọc trên địa chỉ, để chọn sẵn cho khỏi phải chọn lại

    Ô "Đối tượng" đổ động từ CSDL chứ KHÔNG viết cứng 13 dòng: quản trị thêm nhóm
    thứ 13 (hay 14) trong mục Hồ sơ là ô chọn tự có, không phải sửa mã nguồn.
--}}
@php
    $nhomDangXem = null;
    if (!empty($boChon)) {
        $nhomDangXem = $tatCaBo->firstWhere('canonical', $boChon) ?? $tatCaBo->firstWhere('id', (int) $boChon);
    }
    $chonSan = old('interest', $nhomDangXem->name ?? '');
@endphp

<div class="nx-panel" id="nx-nhan-ho-so" style="margin-top:18px;scroll-margin-top:84px">
    <h2 class="nx-panel__title">
        {{ $intro['dossier_form_heading'] ?? 'Điền form để nhận hồ sơ' }}
    </h2>

    <p class="nx__subheading" style="margin-bottom:14px">
        {{ $intro['dossier_form_note'] ?? 'Để lại thông tin, chuyên viên gửi bạn bộ hồ sơ đầy đủ theo đúng nhóm đối tượng — kèm hướng dẫn kê khai từng giấy tờ.' }}
    </p>

    {{-- KHONG dat khoi thong bao o day.
         Gui form xong thi may chu tra nguoi dung ve DAU trang, ma form nay nam o
         CUOI trang - thong bao dat o day thi khong ai nhin thay. Khoi thong bao
         nam o dau cot noi dung, xem dossier/index.blade.php. --}}

    <form method="POST" action="{{ route('noxh.lead.store') }}">
        @csrf
        <input type="hidden" name="source" value="ho-so">

        <div class="nx-field">
            <label for="hs-ten">Họ và tên <span style="color:#dc2626">*</span></label>
            <input type="text" id="hs-ten" name="name" value="{{ old('name') }}"
                   placeholder="Nhập họ và tên" required>
        </div>

        <div class="nx-field">
            <label for="hs-sdt">Số điện thoại <span style="color:#dc2626">*</span></label>
            <input type="tel" id="hs-sdt" name="phone" value="{{ old('phone') }}"
                   placeholder="Nhập số điện thoại" required>
        </div>

        <div class="nx-field">
            <label for="hs-doi-tuong">Đối tượng</label>
            <select id="hs-doi-tuong" name="interest" required>
                <option value="">— Chọn nhóm đối tượng của bạn —</option>
                @foreach($tatCaBo as $bo)
                    <option value="{{ $bo->name }}" {{ $chonSan === $bo->name ? 'selected' : '' }}>
                        {{ $bo->name }}
                    </option>
                @endforeach
            </select>
            <small style="display:block;margin-top:6px;color:#8695aa;font-size:12.5px">
                Chưa rõ mình thuộc nhóm nào?
                <a href="{{ url('/kiem-tra-dieu-kien') }}">Làm bài kiểm tra điều kiện</a> — khoảng 1 phút.
            </small>
        </div>

        <button type="submit" class="nx-btn nx-btn--block">TẢI HỒ SƠ</button>
    </form>

    {{-- Noi ro bam nut thi chuyen gi xay ra. Khong noi thi nguoi dung tuong bam la
         tai duoc file ngay, roi thay trang quay lai cho cu thi tuong hong. --}}
    <p style="margin:12px 0 0;color:#8695aa;font-size:12.5px;line-height:1.6">
        Bấm nút là thông tin được gửi ngay cho chuyên viên phụ trách. Bạn vẫn xem và
        tải được từng mẫu đơn ở phần trên của trang này.
    </p>
</div>
