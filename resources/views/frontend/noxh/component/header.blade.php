{{--
    Dau trang. Thanh dieu huong lay tu bang menus (nhom main-menu) do
    NoxhComposer cap, khong ghi cung trong ma nguon.
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
@endphp

<header class="nx-header">
    <div class="nx-header__inner">
        <a href="{{ url('/') }}" class="nx-header__logo" title="NOXH.vn">
            @if(!empty($system['homepage_logo']))
                <img src="{{ $system['homepage_logo'] }}" alt="{{ $system['homepage_company'] ?? 'NOXH.vn' }}">
            @else
                <span>
                    <span class="nx-header__brand">NOXH<span>.vn</span></span>
                    <span class="nx-header__tagline">{{ $intro['brand_tagline'] ?? 'Rõ pháp lý – Đúng thông tin' }}</span>
                </span>
            @endif
        </a>

        <nav class="nx-header__nav" data-nx-nav>
            @foreach($menuChinh as $muc)
                <a href="{{ $muc['url'] }}"
                   class="nx-header__link{{ $dangXem($muc) ? ' is-active' : '' }}">{{ $muc['name'] }}</a>
            @endforeach
        </nav>

        <div class="nx-header__cta">
            <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--sm">
                TÔI MUỐN MUA NOXH
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
            </a>
        </div>

        <button type="button" class="nx-header__toggle" data-nx-nav-toggle aria-label="Mở menu">
            @include('frontend.noxh.component.icon', ['name' => 'menu', 'size' => 20])
        </button>
    </div>
</header>
