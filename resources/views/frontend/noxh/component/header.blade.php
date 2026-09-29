{{--
    Dau trang. Thanh dieu huong lay tu bang menus (nhom main-menu) do
    NoxhComposer cap, so dien thoai lay tu Cau hinh he thong, khau hieu va
    dong chu trong o tim kiem lay tu module Gioi thieu - khong ghi cung trong
    ma nguon.
--}}
@php
    // Duong dan hien tai de to sang muc dang xem. Bo dau / hai dau cho de so.
    $duongDanHienTai = trim(request()->path(), '/');
    $duongDanHienTai = $duongDanHienTai === '/' ? '' : $duongDanHienTai;

    $dangXem = function ($muc) use ($duongDanHienTai) {
        $c = $muc['canonical'];

        if ($c === '') {
            return $duongDanHienTai === '';
        }

        // Muc cha sang khi dang o bat ky trang con nao cua no.
        return $duongDanHienTai === $c || str_starts_with($duongDanHienTai, $c . '/');
    };

    // O "Hotline" trong quan tri cho phep ghi nhieu so cach nhau bang dau |.
    // Dau trang chi du cho mot so - lay so dau tien.
    $soHotline = nx_hotline_dau($system['contact_hotline'] ?? '');
@endphp

<header class="nx-header">
    <div class="nx-header__inner">
        <a href="{{ url('/') }}" class="nx-header__logo" title="{{ $system['homepage_company'] ?? 'NOXH.vn' }}">
            @if(!empty($system['homepage_logo']))
                <img src="{{ $system['homepage_logo'] }}" alt="{{ $system['homepage_company'] ?? 'NOXH.vn' }}">
            @else
                <span class="nx-header__mark">
                    @include('frontend.noxh.component.icon', ['name' => 'house', 'size' => 24])
                </span>
                <span>
                    <span class="nx-header__brand">{{ $system['homepage_brand'] ?? 'NOXH.vn' }}</span>
                    <span class="nx-header__tagline">{{ $intro['brand_tagline'] ?? '' }}</span>
                </span>
            @endif
        </a>

        <nav class="nx-header__nav" data-nx-nav>
            @foreach($menuChinh as $muc)
                <a href="{{ $muc['url'] }}"
                   class="nx-header__link{{ $dangXem($muc) ? ' is-active' : '' }}">{{ $muc['name'] }}</a>
            @endforeach
        </nav>

        <form class="nx-header__tim" method="GET" action="{{ route('noxh.search') }}" role="search">
            <button type="submit" aria-label="Tìm kiếm">
                @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 17])
            </button>
            <input type="search" name="tu-khoa" value="{{ request('tu-khoa') }}"
                   placeholder="{{ $intro['header_search_placeholder'] ?? 'Tìm dự án, tin tức...' }}"
                   aria-label="Tìm dự án, tin tức">
        </form>

        @if($soHotline)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $soHotline) }}" class="nx-header__phone">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 22])
                <span>
                    <strong>{{ $soHotline }}</strong>
                    <span>{{ $intro['header_phone_note'] ?? 'Tư vấn miễn phí 24/7' }}</span>
                </span>
            </a>
        @endif

        <button type="button" class="nx-header__toggle" data-nx-nav-toggle aria-label="Mở menu">
            @include('frontend.noxh.component.icon', ['name' => 'menu', 'size' => 20])
        </button>
    </div>
</header>
