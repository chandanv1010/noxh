@extends('frontend.noxh.layout')

@section('content')
@php
    // Bon con so o cot phai banner, gom tu cac o quan tri sua duoc.
    $soLieu = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($intro["stat_{$i}_value"])) {
            $soLieu[] = [
                'value' => $intro["stat_{$i}_value"],
                'label' => $intro["stat_{$i}_label"] ?? '',
                'icon' => ['building', 'pin', 'users', 'shield-check'][$i - 1],
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
                'icon' => ['scale', 'folder', 'calculator', 'bank', 'question', 'download'][$i - 1],
            ];
        }
    }

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
<section class="nx-hero">
    @if(!empty($intro['hero_image']))
        <div class="nx-hero__bg">
            <img src="{{ $intro['hero_image'] }}" alt="{{ $intro['hero_title'] ?? '' }}"
                 fetchpriority="high" decoding="async">
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
                            @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17])
                            {{ $intro["hero_usp_{$i}"] }}
                        </li>
                    @endfor
                </ul>
            @endif

            <div class="nx-hero__nut">
                <a href="{{ url('/du-an') }}" class="nx-btn">
                    @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 16])
                    TÌM DỰ ÁN NGAY
                </a>
                <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--ghost">
                    @include('frontend.noxh.component.icon', ['name' => 'clipboard', 'size' => 16])
                    KIỂM TRA ĐIỀU KIỆN
                </a>
            </div>
        </div>

        @if(count($soLieu))
            <div class="nx-hero__so">
                @foreach($soLieu as $s)
                    <div class="nx-so-the">
                        <span class="nx-so-the__icon">
                            @include('frontend.noxh.component.icon', ['name' => $s['icon'], 'size' => 22])
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
            <strong>Tìm dự án nhà ở xã hội</strong>
            <span>Chọn khu vực để xem dự án phù hợp</span>
        </div>

        <div class="nx-tim__o">
            <label for="tim-tinh">Tỉnh / Thành phố</label>
            <select id="tim-tinh" name="province_code">
                <option value="">Tất cả tỉnh/thành</option>
                @foreach($tinhThanh as $t)
                    <option value="{{ $t->province_code }}">{{ $t->province_name }} ({{ $t->so_du_an }})</option>
                @endforeach
            </select>
        </div>

        <div class="nx-tim__o">
            <label for="tim-gia">Khoảng giá</label>
            <select id="tim-gia" name="gia[]">
                <option value="">Tất cả mức giá</option>
                <option value="0-18">Dưới 18 triệu/m²</option>
                <option value="18-20">18 - 20 triệu/m²</option>
                <option value="20-22">20 - 22 triệu/m²</option>
                <option value="22-999">Trên 22 triệu/m²</option>
            </select>
        </div>

        <div class="nx-tim__o">
            <label for="tim-trang-thai">Trạng thái</label>
            <select id="tim-trang-thai" name="status[]">
                <option value="">Tất cả</option>
                @foreach(\App\Models\Product::TRANG_THAI_DU_AN as $ma => $ten)
                    <option value="{{ $ma }}">{{ $ten }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="nx-btn">
            @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 16])
            TÌM DỰ ÁN
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
            <span>{{ $intro['check_description'] ?? 'Trả lời 8 câu hỏi - Chỉ mất khoảng 3 phút - Nhận kết quả ngay' }}</span>
        </div>

        <ul class="nx-moi-kiem-tra__gach">
            <li>@include('frontend.noxh.component.icon', ['name' => 'clock', 'size' => 15]) Nhanh chóng</li>
            <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 15]) Chính xác</li>
            <li>@include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 15]) Bảo mật thông tin</li>
        </ul>

        <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--cam">
            KIỂM TRA NGAY
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 16])
        </a>
    </div>
</div>

{{-- 4. DU AN NOI BAT ------------------------------------------------------- --}}
@if($duAnNoiBat->count())
    <section class="nx__section">
        <div class="nx__container">
            <div class="nx__head">
                <h2 class="nx__heading">Dự án nhà ở xã hội nổi bật</h2>

                @if($tinhCuaDuAn->count() > 1)
                    <div class="nx-the-tinh" data-nx-loc-tinh>
                        <button type="button" class="is-chon" data-tinh="">Tất cả</button>
                        @foreach($tinhCuaDuAn as $ma => $ten)
                            <button type="button" data-tinh="{{ $ma }}">{{ $ten }}</button>
                        @endforeach
                    </div>
                @endif

                <a href="{{ url('/du-an') }}" class="nx__more">Xem tất cả dự án →</a>
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
                                    @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 24])
                                </span>
                                <strong>{{ $o['title'] }}</strong>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="nx-panel" style="margin:0">
                <h2 class="nx-panel__title">
                    Tin tức mới nhất
                    <a href="{{ url('/tin-tuc') }}">Xem tất cả</a>
                </h2>

                @forelse($tinTuc as $bai)
                    <div class="nx-news-item">
                        <a href="{{ url('/tin-tuc/' . $bai->canonical) }}" class="nx-news-item__thumb">
                            @if(!empty($bai->image))
                                <img src="{{ $bai->image }}" alt="{{ $bai->name }}" loading="lazy">
                            @endif
                        </a>
                        <div>
                            <h3 class="nx-news-item__title">
                                <a href="{{ url('/tin-tuc/' . $bai->canonical) }}">{{ $bai->name }}</a>
                            </h3>
                            <time>{{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}</time>
                        </div>
                    </div>
                @empty
                    <p class="nx__subheading" style="margin:0">Chưa có bài viết nào.</p>
                @endforelse
            </div>

        </div>
    </div>
</section>

{{-- 6. TU VAN HO SO TAI KHU VUC CUA BAN ------------------------------------ --}}
@if($nhanVien->count())
    <section class="nx__section">
        <div class="nx__container">
            <div class="nx__head">
                <div>
                    <h2 class="nx__heading">Tư vấn hồ sơ tại khu vực của bạn</h2>
                    <p class="nx__subheading">
                        Đội ngũ tư vấn được NOXH.vn xác minh · Hỗ trợ tận tâm · Hoàn toàn miễn phí
                    </p>
                </div>

                @if($khuVuc->count() > 1)
                    <div class="nx-chon-khu-vuc">
                        <label for="chon-khu-vuc">Chọn khu vực</label>
                        <select id="chon-khu-vuc" data-nx-loc-khu-vuc>
                            <option value="">Tất cả khu vực</option>
                            @foreach($khuVuc as $kv)
                                <option value="{{ $kv }}">{{ $kv }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>

            <div data-nx-danh-sach-tu-van>
                @include('frontend.noxh.component.advisor-list', [
                    'nhanVien' => $nhanVien,
                    'cot' => 6,
                ])
            </div>
        </div>
    </section>
@endif

{{-- 7. DANG KY NHAN TIN ---------------------------------------------------- --}}
<section class="nx-subscribe">
    <div class="nx-subscribe__inner">
        <div class="nx-subscribe__text">
            @include('frontend.noxh.component.icon', ['name' => 'mail', 'size' => 30])
            <span>
                <strong>Đăng ký nhận thông tin mới nhất</strong>
                <span>Cập nhật dự án, chính sách và cơ hội mua NOXH phù hợp với bạn.</span>
            </span>
        </div>

        <form class="nx-subscribe__form" method="POST" action="{{ route('noxh.lead.store') }}">
            @csrf
            <input type="hidden" name="source" value="newsletter">
            <input type="text" name="name" placeholder="Họ và tên *" required>
            <input type="tel" name="phone" placeholder="Số điện thoại *" required>
            <select name="province_code" aria-label="Chọn tỉnh/thành">
                <option value="">Chọn tỉnh/thành</option>
                @foreach($tinhThanh as $t)
                    <option value="{{ $t->province_code }}">{{ $t->province_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="nx-btn nx-btn--on-blue">ĐĂNG KÝ NGAY</button>
        </form>

        <p class="nx-subscribe__note">
            @include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 13])
            Thông tin của bạn được bảo mật tuyệt đối.
        </p>
    </div>
</section>

@include('frontend.noxh.component.advisor-modal')

@push('script')
<script>
(function () {
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

            [].forEach.call(oTuVan.children, function (the) {
                var o = the.querySelector('.nx-advisor__khu');
                var cua = o ? o.textContent.replace('Khu vực:', '').trim() : '';
                the.hidden = kv !== '' && cua !== kv;
            });
        });
    }
})();
</script>
@endpush
@endsection
