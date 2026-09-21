@extends('frontend.noxh.layout')

@section('content')
@php
    // Bon con so tren dai xanh, gom tu cac o quan tri sua duoc.
    $soLieu = [];
    for ($i = 1; $i <= 4; $i++) {
        if (!empty($intro["stat_{$i}_value"])) {
            $soLieu[] = [
                'value' => $intro["stat_{$i}_value"],
                'label' => $intro["stat_{$i}_label"] ?? '',
                'icon' => ['building', 'pin', 'users', 'money'][$i - 1],
            ];
        }
    }

    // Nam buoc cua khoi moi kiem tra dieu kien.
    $buoc = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($intro["check_step_{$i}"])) {
            $buoc[] = [
                'title' => $intro["check_step_{$i}"],
                'desc' => $intro["check_step_{$i}_desc"] ?? '',
                'icon' => ['user', 'home', 'money', 'pin', 'check'][$i - 1],
                'cuoi' => $i === 5,
            ];
        }
    }

    // Nam o "Thong tin huu ich".
    $huuIch = [];
    for ($i = 1; $i <= 5; $i++) {
        if (!empty($intro["useful_{$i}_title"])) {
            $huuIch[] = [
                'title' => $intro["useful_{$i}_title"],
                'desc' => $intro["useful_{$i}_desc"] ?? '',
                'url' => nx_url($intro["useful_{$i}_url"] ?? ''),
                'icon' => ['scale', 'folder', 'calculator', 'bank', 'question'][$i - 1],
            ];
        }
    }
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
        <div>
            @if(!empty($intro['hero_label']))
                <p class="nx-hero__label">{{ $intro['hero_label'] }}</p>
            @endif

            <h1 class="nx-hero__title">{{ $intro['hero_title'] ?? 'Nhà ở xã hội' }}</h1>

            @if(!empty($intro['hero_slogan']))
                <p class="nx-hero__slogan">{{ $intro['hero_slogan'] }}</p>
            @endif

            @if(!empty($intro['hero_description']))
                <p class="nx-hero__description">{{ $intro['hero_description'] }}</p>
            @endif

            @if(!empty($intro['hero_usp_1']))
                <div class="nx-hero__usp">
                    @for($i = 1; $i <= 3; $i++)
                        @continue(empty($intro["hero_usp_{$i}"]))
                        <div class="nx-usp">
                            <span class="nx-usp__icon">
                                @include('frontend.noxh.component.icon', ['name' => ['shield-check', 'scale', 'users'][$i - 1], 'size' => 22])
                            </span>
                            <span>
                                <strong>{{ $intro["hero_usp_{$i}"] }}</strong>
                                <span>{{ $intro["hero_usp_{$i}_desc"] ?? '' }}</span>
                            </span>
                        </div>
                    @endfor
                </div>
            @endif
        </div>

        {{-- O tim du an: gui thang sang trang danh sach bang GET nen ket qua
             chia se duoc bang duong dan. --}}
        <form class="nx-search-box" method="GET" action="{{ url('/du-an') }}">
            <h2 class="nx-search-box__title">Tìm dự án nhà ở xã hội</h2>

            <div class="nx-field">
                <select name="province_code">
                    <option value="">Chọn tỉnh / thành phố</option>
                    @foreach($tinhThanh as $t)
                        <option value="{{ $t->province_code }}">{{ $t->province_name }} ({{ $t->so_du_an }})</option>
                    @endforeach
                </select>
            </div>

            <div class="nx-field">
                <select name="status[]">
                    <option value="">Chọn trạng thái dự án</option>
                    @foreach(\App\Models\Product::TRANG_THAI_DU_AN as $ma => $ten)
                        <option value="{{ $ma }}">{{ $ten }}</option>
                    @endforeach
                </select>
            </div>

            <div class="nx-field">
                <label>Khoảng giá (triệu/m²)</label>
                <div class="nx-search-box__range">
                    <input type="number" name="gia_tu" step="0.1" min="0" placeholder="Từ">
                    <input type="number" name="gia_den" step="0.1" min="0" placeholder="Đến">
                </div>
            </div>

            <button type="submit" class="nx-btn nx-btn--block">
                @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 16])
                TÌM KIẾM
            </button>

            <p class="nx-search-box__foot">
                <a href="{{ url('/du-an') }}">Xem tất cả dự án →</a>
            </p>
        </form>
    </div>
</section>

{{-- 2. DAI SO LIEU -------------------------------------------------------- --}}
@if(count($soLieu))
    <div class="nx-stats nx-stats--overlap">
        <div class="nx-stats__panel">
            @foreach($soLieu as $s)
                <div class="nx-stat">
                    <span class="nx-stat__icon">
                        @include('frontend.noxh.component.icon', ['name' => $s['icon'], 'size' => 26])
                    </span>
                    <span>
                        {{-- Gia tri that de trong data-nx-dem; chu hien ra van la
                             so day du nen tat JS hay trinh doc man hinh van dung. --}}
                        <span class="nx-stat__value" data-nx-dem="{{ $s['value'] }}">{{ $s['value'] }}</span>
                        <span class="nx-stat__label">{{ $s['label'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>
@endif

{{-- 3. MOI KIEM TRA DIEU KIEN ---------------------------------------------- --}}
@if(count($buoc))
    <section class="nx__section">
        <div class="nx__container">
            <div class="nx-check-teaser">
                <div>
                    <h2 class="nx-check-teaser__title">{{ $intro['check_title'] ?? 'Bạn có đủ điều kiện mua nhà ở xã hội?' }}</h2>
                    <p class="nx-check-teaser__description">{{ $intro['check_description'] ?? '' }}</p>

                    <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn">
                        KIỂM TRA NGAY
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 16])
                    </a>

                    <p class="nx-check-teaser__safe">
                        @include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 14])
                        100% thông tin bảo mật
                    </p>
                </div>

                <div class="nx-check-teaser__steps">
                    @foreach($buoc as $i => $b)
                        <div class="nx-step{{ $b['cuoi'] ? ' nx-step--result' : '' }}">
                            <span class="nx-step__icon">
                                @include('frontend.noxh.component.icon', ['name' => $b['icon'], 'size' => 21])
                            </span>
                            <strong>{{ $b['cuoi'] ? $b['title'] : ($i + 1) . '. ' . $b['title'] }}</strong>
                            <span>{{ $b['desc'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

{{-- 4. DU AN NOI BAT ------------------------------------------------------- --}}
@if($duAnNoiBat->count())
    <section class="nx__section">
        <div class="nx__container">
            <div class="nx__head">
                <h2 class="nx__heading">Dự án nổi bật</h2>
                <a href="{{ url('/du-an') }}" class="nx__more">Xem tất cả dự án →</a>
            </div>

            <div class="nx-project-grid">
                @foreach($duAnNoiBat as $duAn)
                    @include('frontend.noxh.component.project-card', ['duAn' => $duAn])
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- 5. THONG TIN HUU ICH --------------------------------------------------- --}}
@if(count($huuIch))
    <section class="nx__section">
        <div class="nx__container">
            <h2 class="nx__heading">{{ $intro['useful_heading'] ?? 'Thông tin hữu ích' }}</h2>
            <p class="nx__subheading">&nbsp;</p>

            <div class="nx-useful">
                @foreach($huuIch as $o)
                    <a href="{{ $o['url'] }}" class="nx-useful-card">
                        <span class="nx-useful-card__icon">
                            @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 26])
                        </span>
                        <strong>{{ $o['title'] }}</strong>
                        <p>{{ $o['desc'] }}</p>
                        <em>Xem chi tiết →</em>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- 6. HOI DAP + TIN TUC + CHUYEN GIA -------------------------------------- --}}
<section class="nx__section">
    <div class="nx__container">
        <div class="nx-home-bottom">

            <div class="nx-panel" style="margin:0">
                <h2 class="nx-panel__title">
                    Câu hỏi nổi bật
                    <a href="{{ url('/hoi-dap') }}">Xem tất cả</a>
                </h2>

                @forelse($cauHoi as $ch)
                    <div class="nx-qa-item">
                        <span class="nx-qa-item__avatar">
                            {{ mb_substr($ch->asker_name ?: 'K', 0, 1) }}
                        </span>
                        <div>
                            <h3 class="nx-qa-item__title">
                                <a href="{{ url('/hoi-dap/' . $ch->id) }}">{{ $ch->title }}</a>
                            </h3>
                            <div class="nx-qa-item__meta">
                                <span>{{ $ch->asker_name ?: 'Bạn đọc' }}</span>
                                <span>{{ $ch->created_at?->diffForHumans() }}</span>
                                <span>
                                    @include('frontend.noxh.component.icon', ['name' => 'eye', 'size' => 13])
                                    {{ number_format($ch->view_count, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="nx__subheading" style="margin:0">Chưa có câu hỏi nào được đăng.</p>
                @endforelse
            </div>

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

            @if($chuyenGia)
                <div class="nx-expert">
                    <h2 class="nx-expert__title">Tư vấn cùng chuyên gia</h2>
                    <p class="nx-expert__name">{{ $chuyenGia->name }}@if($chuyenGia->title) — {{ $chuyenGia->title }}@endif</p>

                    @if($chuyenGia->description)
                        <p class="nx-expert__description">{{ $chuyenGia->description }}</p>
                    @endif

                    @if($chuyenGia->commitments)
                        <ul class="nx-expert__list">
                            @foreach(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $chuyenGia->commitments))) as $ck)
                                <li>
                                    @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16])
                                    {{ $ck }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <a href="{{ url('/cong-hoa/tu-van') }}" class="nx-btn nx-btn--sm">
                        ĐĂNG KÝ TƯ VẤN NGAY
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
                    </a>

                    @if($chuyenGia->image)
                        <div class="nx-expert__photo">
                            <img src="{{ $chuyenGia->image }}" alt="{{ $chuyenGia->name }}" loading="lazy">
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</section>

{{-- 7. DANG KY NHAN TIN ---------------------------------------------------- --}}
<section class="nx-subscribe">
    <div class="nx-subscribe__inner">
        <div class="nx-subscribe__text">
            @include('frontend.noxh.component.icon', ['name' => 'mail', 'size' => 30])
            <span>
                <strong>Nhận thông tin dự án mới nhất</strong>
                <span>Đăng ký để nhận thông tin các dự án NOXH mới, chính sách và ưu đãi mới nhất.</span>
            </span>
        </div>

        <form class="nx-subscribe__form" method="POST" action="{{ route('noxh.lead.store') }}">
            @csrf
            <input type="hidden" name="source" value="newsletter">
            <input type="text" name="name" placeholder="Họ và tên của bạn" required>
            <input type="tel" name="phone" placeholder="Số điện thoại" required>
            <button type="submit" class="nx-btn nx-btn--on-blue">ĐĂNG KÝ NGAY</button>
        </form>
    </div>
</section>
@endsection
