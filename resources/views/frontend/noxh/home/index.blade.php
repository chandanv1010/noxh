@extends('frontend.noxh.layout')

@section('content')
@php
    use App\Classes\NoxhIcon;

    // Hinh do quan tri chon. Neu chua chon hoac chon phai ten khong con
    // trong bo hinh thi dung hinh mac dinh cua o do - khong de trong lam vo
    // hang.
    $hinh = function ($khoa, $macDinh) use ($intro) {
        $ten = $intro[$khoa] ?? '';

        return NoxhIcon::hopLe($ten) ? $ten : $macDinh;
    };

    // Bon dong so lieu o bang ben phai banner.
    $soLieu = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($intro["stat_{$i}_value"])) {
            $soLieu[] = [
                'value' => $intro["stat_{$i}_value"],
                'label' => $intro["stat_{$i}_label"] ?? '',
                'icon' => $hinh("stat_{$i}_icon", ['building', 'pin', 'users', 'shield-check'][$i - 1]),
            ];
        }
    }

    // Ba diem nhan tren dai moi kiem tra dieu kien.
    $diemNhan = [];
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($intro["check_point_{$i}"])) {
            $diemNhan[] = [
                'text' => $intro["check_point_{$i}"],
                'icon' => $hinh("check_point_{$i}_icon", ['clock', 'shield-check', 'lock'][$i - 1]),
            ];
        }
    }

    // Sau o "Thong tin huu ich".
    $huuIch = [];
    for ($i = 1; $i <= 6; $i++) {
        if (!empty($intro["useful_{$i}_title"])) {
            $huuIch[] = [
                'title' => $intro["useful_{$i}_title"],
                'url' => nx_url($intro["useful_{$i}_url"] ?? ''),
                'icon' => $hinh("useful_{$i}_icon", ['scale', 'clipboard', 'coins', 'bulb', 'question', 'download'][$i - 1]),
            ];
        }
    }

    // Cac khoang gia trong o chon thu ba. Quan tri go moi dong mot khoang
    // theo dang  "Nhan | tu-den"  nen phai tach ra o day.
    $khoangGia = nx_khoang_gia($intro['search_price_ranges'] ?? '');

    // The tinh/thanh tren khoi du an noi bat: chi lay tinh THUC SU co du an
    // trong danh sach dang hien, khong thi bam vao the ra khoi rong.
    $tinhCuaDuAn = $duAnNoiBat->pluck('province_name', 'province_code')
        ->filter()
        ->unique()
        ->take(7);

    // Khu vuc cua doi tu van, de loc the nhan vien.
    $khuVuc = $nhanVien->pluck('address')->filter()->unique()->values();
@endphp

{{-- 1. BANNER ------------------------------------------------------------- --}}
@php
    // Anh banner doc cho dien thoai, theo thu tu uu tien:
    //   1. o "Anh nen banner (dien thoai)" trong Cau hinh -> Gioi thieu  <-- cho sua CHINH
    //   2. slide nhom 'mobile-slide'  (du phong, khi o tren de trong)
    //   3. anh may tinh
    // O trong Cau hinh -> Gioi thieu phai dung TRUOC: do moi la cho quan tri
    // nhin thay va sua. De slide dung truoc thi sua o do khong thay gi doi.
    $anhBannerDoc = $intro['hero_image_mobile'] ?? ($slideBannerMobile['item'][0]['image'] ?? '');
    $anhBannerDoc = trim((string) $anhBannerDoc);
@endphp
<section class="nx-hero">
    @if(!empty($intro['hero_image']))
        {{-- Hai ban anh banner: ban ngang cho man hinh rong, ban DOC cho dien
             thoai. Anh ngang dat doc tren dien thoai thi toa nha chi con mot
             dai mong, khong du cho de dat cac dong chu len tren.

             Dung <picture> chu khong phai hai the <img> an/hien: trinh duyet
             chi tai DUNG mot anh no can, cach kia tai ca hai. --}}
        <div class="nx-hero__bg">
            <picture>
                @if($anhBannerDoc !== '')
                    <source media="(max-width: 1024px)" srcset="{{ nx_anh($anhBannerDoc, 'du-an') }}">
                @endif
                <img src="{{ nx_anh($intro['hero_image'], 'du-an') }}" alt="{{ $intro['hero_title'] ?? '' }}"
                     fetchpriority="high" decoding="async">
            </picture>
        </div>
    @endif

    <div class="nx-hero__inner">
        <div class="nx-hero__chu">
            @if(!empty($intro['hero_label']))
                <p class="nx-hero__label">{{ $intro['hero_label'] }}</p>
            @endif

            <h1 class="nx-hero__title">{{ $intro['hero_title'] ?? 'Nhà ở xã hội' }}</h1>

            @if(!empty($intro['hero_slogan']))
                <p class="nx-hero__slogan">{{ $intro['hero_slogan'] }}</p>
            @endif

            @if(!empty($intro['hero_usp_1']))
                <ul class="nx-hero__gach">
                    @for($i = 1; $i <= 3; $i++)
                        @continue(empty($intro["hero_usp_{$i}"]))
                        <li>
                            @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 19])
                            {{ $intro["hero_usp_{$i}"] }}
                        </li>
                    @endfor
                </ul>
            @endif

            <div class="nx-hero__nut">
                <a href="{{ nx_url($intro['hero_btn_1_url'] ?? 'du-an') }}" class="nx-btn">
                    @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 18])
                    {{ $intro['hero_btn_1_text'] ?? 'TÌM DỰ ÁN NGAY' }}
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 17])
                </a>
                <a href="{{ nx_url($intro['hero_btn_2_url'] ?? 'kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--ghost">
                    @include('frontend.noxh.component.icon', ['name' => 'file', 'size' => 18])
                    {{ $intro['hero_btn_2_text'] ?? 'KIỂM TRA ĐIỀU KIỆN' }}
                </a>
            </div>
        </div>

        {{--
            Khoi "Bang so lieu canh banner" dang duoc AN TAM - ca may tinh lan
            dien thoai.

            Du lieu van duoc dung san o $soLieu (Cau hinh -> Gioi thieu, nhom
            "Khoi 1b") nen khong phai nhap lai gi. Muon hien lai thi bo doan
            `false &&` trong dieu kien duoi day.

            CSS cua khoi nay (.nx-hero__so, .nx-so-the) giu nguyen, khong xoa.
        --}}
        @if(false && count($soLieu))
            <div class="nx-hero__so">
                @foreach($soLieu as $s)
                    <div class="nx-so-the">
                        <span class="nx-so-the__icon">
                            @include('frontend.noxh.component.icon', ['name' => $s['icon'], 'size' => 24])
                        </span>
                        <span>
                            {{-- Gia tri that de trong data-nx-dem; chu hien ra van la so
                                 day du nen tat JS hay trinh doc man hinh van dung. --}}
                            <strong data-nx-dem="{{ $s['value'] }}">{{ $s['value'] }}</strong>
                            <span>{{ $s['label'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- 2. THANH TIM DU AN ----------------------------------------------------- --}}
<div class="nx__container">
    <form class="nx-tim" method="GET" action="{{ url('/du-an') }}">
        <div class="nx-tim__nhan">
            <strong>{{ $intro['search_title'] ?? 'Tìm dự án nhà ở xã hội' }}</strong>
            <span>{{ $intro['search_description'] ?? 'Chọn khu vực để xem dự án phù hợp' }}</span>
        </div>

        <div class="nx-tim__o">
            <label for="tim-tinh">Tỉnh / Thành phố</label>
            <select id="tim-tinh" name="province_code" data-nx-tinh data-nx-chon>
                <option value="">Chọn tỉnh/thành phố</option>
                @foreach($tinhThanh as $t)
                    {{-- Chi ghi so khi tinh do THUC SU co du an: in "(0)" sau
                         ba chuc dong lam danh sach roi ram ma khong them tin
                         gi. --}}
                    <option value="{{ $t->province_code }}">{{ nx_ten_dia_gioi_ngan($t->province_name) }}@if($t->so_du_an > 0) ({{ $t->so_du_an }})@endif</option>
                @endforeach
            </select>
        </div>

        {{-- Tu 01/07/2025 Viet Nam bo cap quan/huyen: duoi tinh/thanh la
             thang phuong/xa. Danh sach 3.321 phuong/xa khong nhet vao trang
             ma goi rieng khi nguoi dung chon tinh. --}}
        <div class="nx-tim__o">
            <label for="tim-xa">Phường / Xã</label>
            <select id="tim-xa" name="ward_code" data-nx-xa data-nx-chon disabled>
                <option value="">Chọn tỉnh/thành trước</option>
            </select>
        </div>

        <div class="nx-tim__o">
            <label for="tim-gia">Khoảng giá</label>
            <select id="tim-gia" name="gia[]" data-nx-chon>
                <option value="">Chọn khoảng giá</option>
                @foreach($khoangGia as $g)
                    <option value="{{ $g['value'] }}">{{ $g['label'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="nx-tim__o">
            <label for="tim-trang-thai">Trạng thái</label>
            <select id="tim-trang-thai" name="status[]" data-nx-chon>
                <option value="">Tất cả</option>
                @foreach(\App\Models\Product::TRANG_THAI_DU_AN as $ma => $ten)
                    <option value="{{ $ma }}">{{ $ten }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="nx-btn">
            @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 17])
            {{ $intro['search_button'] ?? 'TÌM DỰ ÁN' }}
        </button>
    </form>
</div>

{{-- 3. MOI KIEM TRA DIEU KIEN ---------------------------------------------- --}}
<div class="nx__container">
    <div class="nx-moi-kiem-tra">
        <span class="nx-moi-kiem-tra__icon">
            @include('frontend.noxh.component.icon', ['name' => 'clipboard', 'size' => 30])
        </span>

        <div class="nx-moi-kiem-tra__chu">
            <strong>{{ $intro['check_title'] ?? 'Bạn có đủ điều kiện mua NOXH?' }}</strong>
            <span>{{ $intro['check_description'] ?? '' }}</span>
        </div>

        @if(count($diemNhan))
            <ul class="nx-moi-kiem-tra__gach">
                @foreach($diemNhan as $d)
                    <li>
                        <i>@include('frontend.noxh.component.icon', ['name' => $d['icon'], 'size' => 16])</i>
                        {{ $d['text'] }}
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ nx_url($intro['check_button_url'] ?? 'kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--cam">
            {{ $intro['check_button_text'] ?? 'KIỂM TRA NGAY' }}
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 17])
        </a>
    </div>
</div>

{{-- 4. DU AN NOI BAT ------------------------------------------------------- --}}
@if($duAnNoiBat->count())
    <section class="nx__section">
        <div class="nx__container">
            <div class="nx__head">
                <h2 class="nx__heading">{{ $intro['project_block_heading'] ?? 'Dự án nhà ở xã hội nổi bật' }}</h2>

                @if($tinhCuaDuAn->count() > 1)
                    <div class="nx-the-tinh" data-nx-loc-tinh>
                        <button type="button" class="is-chon" data-tinh="">Tất cả</button>
                        @foreach($tinhCuaDuAn as $ma => $ten)
                            <button type="button" data-tinh="{{ $ma }}">{{ nx_ten_dia_gioi_ngan($ten) }}</button>
                        @endforeach
                    </div>
                @endif

                <a href="{{ url('/du-an') }}" class="nx__more">
                    {{ $intro['project_more_text'] ?? 'Xem tất cả dự án' }}
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
                </a>
            </div>

            <div class="nx-project-grid nx-project-grid--4" data-nx-danh-sach-du-an>
                @foreach($duAnNoiBat as $duAn)
                    <div data-tinh="{{ $duAn->province_code }}">
                        @include('frontend.noxh.component.project-card', ['duAn' => $duAn])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- 5. THONG TIN HUU ICH + TIN TUC ----------------------------------------- --}}
<section class="nx__section">
    <div class="nx__container">
        <div class="nx-hai-cot">

            @if(count($huuIch))
                <div class="nx-panel" style="margin:0">
                    <h2 class="nx-panel__title">{{ $intro['useful_heading'] ?? 'Thông tin hữu ích' }}</h2>

                    <div class="nx-o-huu-ich">
                        @foreach($huuIch as $o)
                            <a href="{{ $o['url'] }}" class="nx-o-huu-ich__the">
                                <span>
                                    @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 27])
                                </span>
                                <strong>{{ $o['title'] }}</strong>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="nx-panel" style="margin:0">
                <h2 class="nx-panel__title">
                    {{ $intro['news_block_heading'] ?? 'Tin tức mới nhất' }}
                    <a href="{{ url('/tin-tuc') }}">
                        {{ $intro['news_more_text'] ?? 'Xem tất cả' }}
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 13])
                    </a>
                </h2>

                @if($tinTuc->count())
                    <div class="nx-tin-hang">
                        @foreach($tinTuc as $bai)
                            @include('frontend.noxh.component.news-item', ['bai' => $bai, 'cot' => true])
                        @endforeach
                    </div>
                @else
                    <p class="nx__subheading" style="margin:0">Chưa có bài viết nào.</p>
                @endif
            </div>

        </div>
    </div>
</section>

{{-- 6. TU VAN HO SO TAI KHU VUC CUA BAN ------------------------------------ --}}
@if($nhanVien->count())
    <section class="nx__section">
        <div class="nx__container">
          <div class="nx-doi-tu-van">
            <div class="nx__head">
                <div>
                    <h2 class="nx__heading">{{ $intro['advisor_heading'] ?? 'Tư vấn hồ sơ tại khu vực của bạn' }}</h2>
                    @if(!empty($intro['advisor_description']))
                        <p class="nx__subheading">{{ $intro['advisor_description'] }}</p>
                    @endif
                </div>

                @if($khuVuc->count() > 1)
                    <div class="nx-chon-khu-vuc">
                        <label for="chon-khu-vuc">Chọn khu vực</label>
                        <select id="chon-khu-vuc" data-nx-loc-khu-vuc data-nx-chon>
                            <option value="">Tất cả khu vực</option>
                            @foreach($khuVuc as $kv)
                                <option value="{{ $kv }}">{{ $kv }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <a href="{{ url('/cong-hoa/tu-van') }}" class="nx__more">
                    {{ $intro['advisor_more_text'] ?? 'Xem tất cả tư vấn viên' }}
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
                </a>
            </div>

            <div data-nx-danh-sach-tu-van>
                @include('frontend.noxh.component.advisor-list', [
                    'nhanVien' => $nhanVien,
                    'cot' => 6,
                ])
            </div>
          </div>
        </div>
    </section>
@endif

{{-- 7. DANG KY NHAN TIN ---------------------------------------------------- --}}
<section class="nx-subscribe">
    <div class="nx-subscribe__anh">
        <img src="{{ nx_anh($intro['subscribe_image'] ?? null, 'dang-ky') }}" alt=""
             loading="lazy" decoding="async">
    </div>

    <div class="nx-subscribe__inner">
        <div class="nx-subscribe__text">
            <span>@include('frontend.noxh.component.icon', ['name' => 'send', 'size' => 26])</span>
            <span>
                <strong>{{ $intro['subscribe_title'] ?? 'Đăng ký nhận thông tin mới nhất' }}</strong>
                <span>{{ $intro['subscribe_description'] ?? '' }}</span>
            </span>
        </div>

        <form class="nx-subscribe__form" method="POST" action="{{ route('noxh.lead.store') }}">
            @csrf
            <input type="hidden" name="source" value="newsletter">
            <input type="text" name="name" placeholder="Họ và tên *" required>
            <input type="tel" name="phone" placeholder="Số điện thoại *" required>
            <select name="province_code" aria-label="Chọn tỉnh/thành" data-nx-chon>
                <option value="">Chọn tỉnh/thành</option>
                @foreach($tinhThanh as $t)
                    <option value="{{ $t->province_code }}">{{ nx_ten_dia_gioi_ngan($t->province_name) }}</option>
                @endforeach
            </select>
            <button type="submit" class="nx-btn">{{ $intro['subscribe_button'] ?? 'ĐĂNG KÝ NGAY' }}</button>
        </form>

        @if(!empty($intro['subscribe_note']))
            <p class="nx-subscribe__note">
                @include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 14])
                {{ $intro['subscribe_note'] }}
            </p>
        @endif
    </div>
</section>

@include('frontend.noxh.component.advisor-modal')

@push('script')
<script>
(function () {
    // --- O chon phuong/xa doi theo tinh dang chon ---------------------------
    var oTinh = document.querySelector('[data-nx-tinh]');
    var oXa = document.querySelector('[data-nx-xa]');

    if (oTinh && oXa) {
        var GOC = @json(url('/dia-gioi/phuong-xa'));
        var daTai = {};

        var ve = function (ds) {
            oXa.innerHTML = '';
            var dau = document.createElement('option');
            dau.value = '';
            dau.textContent = ds.length ? 'Tất cả phường/xã' : 'Chọn phường/xã';
            oXa.appendChild(dau);

            ds.forEach(function (x) {
                var o = document.createElement('option');
                o.value = x.code;
                o.textContent = x.name;
                oXa.appendChild(o);
            });

            oXa.disabled = ds.length === 0;
        };

        oTinh.addEventListener('change', function () {
            var ma = oTinh.value;

            if (!ma) {
                oXa.innerHTML = '<option value="">Chọn tỉnh/thành trước</option>';
                oXa.disabled = true;
                return;
            }

            // Moi tinh chi tai mot lan trong mot luot xem trang.
            if (daTai[ma]) {
                ve(daTai[ma]);
                return;
            }

            oXa.disabled = true;
            oXa.innerHTML = '<option value="">Đang tải…</option>';

            fetch(GOC + '/' + encodeURIComponent(ma))
                .then(function (r) { return r.ok ? r.json() : []; })
                .catch(function () { return []; })
                .then(function (ds) {
                    daTai[ma] = ds || [];
                    ve(daTai[ma]);
                });
        });
    }

    // --- Loc du an theo the tinh/thanh --------------------------------------
    var thanhThe = document.querySelector('[data-nx-loc-tinh]');
    var oDuAn = document.querySelector('[data-nx-danh-sach-du-an]');

    if (thanhThe && oDuAn) {
        // Moi the chi hien toi da 4 du an - dung so cot cua luoi, khong thi
        // bam "Tat ca" se do ra ca 12 cai lam vo bo cuc.
        var TOI_DA = 4;

        var loc = function (ma) {
            var hien = 0;

            [].forEach.call(oDuAn.children, function (o) {
                var khop = !ma || o.getAttribute('data-tinh') === ma;
                var con = khop && hien < TOI_DA;
                o.hidden = !con;
                if (con) hien++;
            });
        };

        thanhThe.addEventListener('click', function (e) {
            var nut = e.target.closest('button[data-tinh]');
            if (!nut) return;

            [].forEach.call(thanhThe.children, function (b) { b.classList.remove('is-chon'); });
            nut.classList.add('is-chon');
            loc(nut.getAttribute('data-tinh'));
        });

        loc('');
    }

    // --- Loc tu van vien theo khu vuc ---------------------------------------
    var oChon = document.querySelector('[data-nx-loc-khu-vuc]');
    var oTuVan = document.querySelector('[data-nx-danh-sach-tu-van] .nx-advisors');

    if (oChon && oTuVan) {
        oChon.addEventListener('change', function () {
            var kv = oChon.value;
            var conLai = 0;

            [].forEach.call(oTuVan.children, function (the) {
                var o = the.querySelector('.nx-advisor__khu');
                var cua = o ? o.textContent.replace('Khu vực:', '').trim() : '';
                the.hidden = kv !== '' && cua !== kv;

                if (!the.hidden) { conLai++; }
            });

            // Chi con MOT nguoi thi cho the chiem ca be ngang. Luoi 2 cot de
            // nguyen thi mot nua man hinh trong tron, nhin nhu loi.
            oTuVan.classList.toggle('nx-advisors--mot', conLai === 1);
        });
    }
})();
</script>
@endpush
@endsection
