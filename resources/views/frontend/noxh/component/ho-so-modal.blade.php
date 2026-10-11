{{--
    Popup "Download trọn bộ" — hiện lên khi bấm nút tải cả bộ hồ sơ.

    In MỘT lần cho cả trang. Mỗi nút tải chỉ cần mang theo
    data-nx-tron-bo="<tên bộ hồ sơ>" và href trỏ tới tệp ZIP của bộ đó — JS ở
    dưới đọc thẳng từ nút vừa bấm.

    Luồng khi bấm "Download hồ sơ":
      1. Gửi tên / số điện thoại / nhóm đối tượng cho chuyên viên (fetch).
      2. Gửi xong mới bắt đầu tải tệp ZIP của bộ hồ sơ vừa bấm.
    Tách làm hai bước vì trình duyệt không cho vừa tải tệp vừa báo lỗi kiểm tra
    dữ liệu trong cùng một lần gửi — sai số điện thoại là người dùng mất trang.

    Ô "Đối tượng" đổ động từ CSDL chứ KHÔNG viết cứng 13 dòng: đây chính là
    danh sách đáp án bước "Đối tượng" của bộ kiểm tra điều kiện, quản trị sửa
    ở màn hình "Đáp án điều kiện" là cả hai nơi cùng đổi.

    Tham số:
      $doiTuong  danh sách nhóm đối tượng chính sách (chuỗi tên)
      $intro     các ô chữ quản trị sửa được
--}}
<div class="nx-modal" data-nx-ho-so-modal hidden>
    <div class="nx-modal__nen" data-nx-hs-dong></div>

    <div class="nx-modal__hop" role="dialog" aria-modal="true" aria-labelledby="nx-hs-tieu-de">
        <button type="button" class="nx-modal__dong" data-nx-hs-dong aria-label="Đóng">&times;</button>

        <h2 class="nx-modal__tieu-de" id="nx-hs-tieu-de">
            {{ $intro['dossier_modal_heading'] ?? 'Tải trọn bộ hồ sơ' }}
        </h2>
        <p class="nx-modal__phu">
            {{ $intro['dossier_modal_note'] ?? 'Để lại thông tin để chuyên viên hỗ trợ bạn kê khai. Tệp hồ sơ sẽ tải về ngay sau khi gửi.' }}
            <strong data-nx-hs-ten-bo></strong>
        </p>

        {{-- Van tro dung route that: JS hong hay bi chan thi form nay van gui
             duoc theo cach thuong, va nguoi dung van tai duoc ho so bang chinh
             duong dan tren the <a> cua nut. --}}
        <form method="POST" action="{{ route('noxh.lead.store') }}" data-nx-hs-form>
            @csrf
            <input type="hidden" name="source" value="ho-so-tron-bo">
            {{-- Ten bo ho so khach dang tai - de chuyen vien biet khach quan tam
                 giay to gi, khong phai doan. --}}
            <input type="hidden" name="message" value="" data-nx-hs-bo>

            <div class="nx-modal__o">
                <label for="nx-hs-ten">Tên <span>*</span></label>
                <input type="text" id="nx-hs-ten" name="name" required maxlength="191"
                       placeholder="Nhập họ và tên" autocomplete="name">
            </div>

            <div class="nx-modal__o">
                <label for="nx-hs-sdt">Số điện thoại <span>*</span></label>
                <input type="tel" id="nx-hs-sdt" name="phone" required maxlength="20"
                       placeholder="Nhập số điện thoại" autocomplete="tel">
            </div>

            <div class="nx-modal__o">
                <label for="nx-hs-doi-tuong">Đối tượng <span>*</span></label>
                <select id="nx-hs-doi-tuong" name="interest" required>
                    <option value="">— Chọn nhóm đối tượng của bạn —</option>
                    @foreach($doiTuong as $nhom)
                        <option value="{{ $nhom }}">{{ $nhom }}</option>
                    @endforeach
                </select>
            </div>

            <p class="nx-modal__bao" data-nx-hs-bao hidden></p>

            <button type="submit" class="nx-btn nx-btn--block" data-nx-hs-gui>
                @include('frontend.noxh.component.icon', ['name' => 'download', 'size' => 16])
                {{ $intro['dossier_modal_button'] ?? 'DOWNLOAD HỒ SƠ' }}
            </button>

            <p class="nx-modal__chan">
                @include('frontend.noxh.component.icon', ['name' => 'shield-check', 'size' => 14])
                Thông tin của bạn được bảo mật, chỉ dùng để liên hệ tư vấn.
            </p>
        </form>
    </div>
</div>

@push('script')
<script>
(function () {
    var hop = document.querySelector('[data-nx-ho-so-modal]');
    if (!hop) return;

    var form = hop.querySelector('[data-nx-hs-form]');
    var bao = hop.querySelector('[data-nx-hs-bao]');
    var nut = hop.querySelector('[data-nx-hs-gui]');
    var oTenBo = hop.querySelector('[data-nx-hs-bo]');
    var chuTenBo = hop.querySelector('[data-nx-hs-ten-bo]');
    var duongTai = null;
    var moBoi = null;

    var hienBao = function (chu, hong) {
        bao.textContent = chu;
        bao.hidden = !chu;
        bao.className = 'nx-modal__bao' + (hong ? ' is-loi' : ' is-xong');
    };

    var mo = function (nutMo) {
        form.reset();
        hienBao('', false);

        moBoi = nutMo;
        duongTai = nutMo.getAttribute('href');

        var tenBo = nutMo.getAttribute('data-nx-tron-bo') || '';
        oTenBo.value = tenBo ? 'Tải trọn bộ: ' + tenBo : '';
        chuTenBo.textContent = tenBo ? 'Bộ hồ sơ: ' + tenBo : '';

        hop.hidden = false;
        document.body.classList.add('nx-khoa-cuon');

        var oTen = hop.querySelector('#nx-hs-ten');
        if (oTen) oTen.focus();
    };

    var dong = function () {
        hop.hidden = true;
        document.body.classList.remove('nx-khoa-cuon');
        // Tra tieu diem ve dung nut da mo popup - nguoi dung ban phim khong bi
        // mat dau nhay ve dau trang.
        if (moBoi) moBoi.focus();
    };

    document.addEventListener('click', function (e) {
        var nutMo = e.target.closest('[data-nx-tron-bo]');
        if (nutMo) {
            e.preventDefault();
            mo(nutMo);
            return;
        }

        if (e.target.closest('[data-nx-hs-dong]')) {
            e.preventDefault();
            dong();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !hop.hidden) dong();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!duongTai) {
            hienBao('Không xác định được bộ hồ sơ cần tải.', true);
            return;
        }

        var tai = duongTai;

        nut.disabled = true;
        hienBao('Đang gửi…', false);

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(form),
        })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (kq) {
                if (kq.ok && kq.d.xong) {
                    hienBao('Đã gửi thông tin. Hồ sơ đang được tải về…', false);
                    // Dieu huong sang tep ZIP: trinh duyet tai tep ve chu khong
                    // roi khoi trang dang xem.
                    window.location.href = tai;
                    setTimeout(dong, 2500);
                    return;
                }

                // Loi kiem tra du lieu tra ve dang {errors: {phone: [...]}}
                var chu = kq.d.loiNhan;
                if (!chu && kq.d.errors) {
                    chu = Object.keys(kq.d.errors).map(function (k) { return kq.d.errors[k][0]; }).join(' ');
                }
                hienBao(chu || 'Không gửi được, bạn thử lại giúp tôi.', true);
            })
            .catch(function () {
                hienBao('Không kết nối được máy chủ. Bạn thử lại hoặc gọi hotline giúp tôi.', true);
            })
            .then(function () { nut.disabled = false; });
    });
})();
</script>
@endpush
