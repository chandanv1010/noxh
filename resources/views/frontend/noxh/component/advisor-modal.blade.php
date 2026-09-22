{{--
    Popup xin tu van tu mot nhan vien kinh doanh cu the.

    Chi in MOT lan cho ca trang. Moi nut "Lien he" chi can mang theo
    data-nx-lien-he="<id nhan vien>" va data-nx-ten="<ten>" - JS o duoi tu do
    dien vao form.

    Form gui bang fetch de khong phai tai lai ca trang, nhung the <form> van
    tro dung route that: JS hong hay bi chan thi no van gui duoc theo cach
    thuong.
--}}
<div class="nx-modal" data-nx-modal hidden>
    <div class="nx-modal__nen" data-nx-dong></div>

    <div class="nx-modal__hop" role="dialog" aria-modal="true" aria-labelledby="nx-modal-tieu-de">
        <button type="button" class="nx-modal__dong" data-nx-dong aria-label="Đóng">&times;</button>

        <h2 class="nx-modal__tieu-de" id="nx-modal-tieu-de">Đăng ký tư vấn</h2>
        <p class="nx-modal__phu">
            Để lại thông tin, <strong data-nx-ten-nhan-vien>chuyên viên</strong> sẽ gọi lại cho bạn.
        </p>

        <form method="POST" action="{{ route('noxh.lead.advisor') }}" data-nx-form-tu-van>
            @csrf
            <input type="hidden" name="nhan_vien_id" value="" data-nx-nhan-vien-id>
            <input type="hidden" name="product_id" value="{{ $duAnId ?? '' }}">

            <div class="nx-modal__o">
                <label for="nx-modal-ten">Họ và tên <span>*</span></label>
                <input type="text" id="nx-modal-ten" name="name" required maxlength="191"
                       placeholder="Ví dụ: Nguyễn Văn A" autocomplete="name">
            </div>

            <div class="nx-modal__o">
                <label for="nx-modal-sdt">Số điện thoại <span>*</span></label>
                <input type="tel" id="nx-modal-sdt" name="phone" required maxlength="20"
                       placeholder="Ví dụ: 0901 234 567" autocomplete="tel">
            </div>

            <div class="nx-modal__o">
                <label for="nx-modal-ghi-chu">Ghi chú <em>(không bắt buộc)</em></label>
                <textarea id="nx-modal-ghi-chu" name="message" rows="3" maxlength="2000"
                          placeholder="Bạn quan tâm dự án nào, cần hỗ trợ gì?"></textarea>
            </div>

            <p class="nx-modal__bao" data-nx-bao hidden></p>

            <button type="submit" class="nx-btn nx-btn--block" data-nx-gui>
                @include('frontend.noxh.component.icon', ['name' => 'send', 'size' => 16])
                GỬI THÔNG TIN
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
    var hop = document.querySelector('[data-nx-modal]');
    if (!hop) return;

    var form = hop.querySelector('[data-nx-form-tu-van]');
    var oId = hop.querySelector('[data-nx-nhan-vien-id]');
    var oTen = hop.querySelector('[data-nx-ten-nhan-vien]');
    var bao = hop.querySelector('[data-nx-bao]');
    var nut = hop.querySelector('[data-nx-gui]');
    var moBoi = null;

    var hienBao = function (chu, hong) {
        bao.textContent = chu;
        bao.hidden = !chu;
        bao.className = 'nx-modal__bao' + (hong ? ' is-loi' : ' is-xong');
    };

    var mo = function (id, ten, boi) {
        oId.value = id;
        oTen.textContent = ten || 'chuyên viên';
        moBoi = boi || null;
        hienBao('', false);
        form.reset();
        oId.value = id;
        hop.hidden = false;
        document.body.classList.add('nx-khoa-cuon');

        var oTenKhach = hop.querySelector('#nx-modal-ten');
        if (oTenKhach) oTenKhach.focus();
    };

    var dong = function () {
        hop.hidden = true;
        document.body.classList.remove('nx-khoa-cuon');
        // Tra tieu diem ve dung nut da mo popup - nguoi dung ban phim khong bi
        // mat dau nhay ve dau trang.
        if (moBoi) moBoi.focus();
    };

    document.addEventListener('click', function (e) {
        var nutMo = e.target.closest('[data-nx-lien-he]');
        if (nutMo) {
            e.preventDefault();
            mo(nutMo.getAttribute('data-nx-lien-he'), nutMo.getAttribute('data-nx-ten'), nutMo);
            return;
        }

        if (e.target.closest('[data-nx-dong]')) {
            e.preventDefault();
            dong();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !hop.hidden) dong();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

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
                    hienBao(kq.d.loiNhan, false);
                    form.reset();
                    setTimeout(dong, 2200);
                } else {
                    // Loi kiem tra du lieu tra ve dang {errors: {phone: [...]}}
                    var chu = kq.d.loiNhan;
                    if (!chu && kq.d.errors) {
                        chu = Object.keys(kq.d.errors).map(function (k) { return kq.d.errors[k][0]; }).join(' ');
                    }
                    hienBao(chu || 'Không gửi được, bạn thử lại giúp tôi.', true);
                }
            })
            .catch(function () {
                hienBao('Không kết nối được máy chủ. Bạn thử lại hoặc gọi hotline giúp tôi.', true);
            })
            .then(function () { nut.disabled = false; });
    });
})();
</script>
@endpush
