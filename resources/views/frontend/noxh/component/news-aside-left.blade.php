{{--
    Cot trai cua hai trang tin tuc: danh sach chuyen muc va the "Cần tư vấn
    thêm?". Trang danh muc va trang chi tiet dung chung khoi nay.

    $danhMuc - cac chuyen muc (kem icon, canonical)
    $dangMo  - id chuyen muc dang xem, null la dang o muc "tat ca"
--}}
@php
    $soDT = nx_hotline_dau($system['contact_hotline'] ?? '');
    $nutTuVan = trim((string) ($intro['news_help_link'] ?? '')) ?: '/cong-hoa/tu-van';
@endphp

<div class="nx-panel nx-tin-muc">
    <h2 class="nx-tin-muc__dau">{{ $intro['news_cat_heading'] ?? 'Danh mục tin tức' }}</h2>

    <nav class="nx-tin-muc__ds">
        <a href="{{ url('/tin-tuc') }}"
           class="nx-tin-muc__o{{ empty($dangMo) ? ' dang-xem' : '' }}">
            @include('frontend.noxh.component.icon', [
                'name' => $intro['news_cat_all_icon'] ?? 'news-all', 'size' => 20,
            ])
            <span>{{ $intro['news_cat_all_text'] ?? 'Tất cả tin tức' }}</span>
        </a>

        @foreach($danhMuc as $muc)
            <a href="{{ url('/tin-tuc/chuyen-muc/' . $muc->canonical) }}"
               class="nx-tin-muc__o{{ (int) ($dangMo ?? 0) === (int) $muc->id ? ' dang-xem' : '' }}">
                @if(\App\Classes\NoxhIcon::hopLe($muc->icon))
                    @include('frontend.noxh.component.icon', ['name' => $muc->icon, 'size' => 20])
                @else
                    {{-- Chuyen muc chua chon hinh: chua mot o trong dung be ngang
                         de ten cac muc van thang hang voi nhau. --}}
                    <i class="nx-tin-muc__khong" aria-hidden="true"></i>
                @endif
                <span>{{ $muc->name }}</span>
            </a>
        @endforeach
    </nav>
</div>

@if(!empty($intro['news_help_heading']))
    <div class="nx-panel nx-tin-hotro">
        <div class="nx-tin-hotro__dau">
            @if(!empty($intro['news_help_icon']))
                <span class="nx-tin-hotro__icon">
                    @include('frontend.noxh.component.icon', [
                        'name' => $intro['news_help_icon'], 'size' => 26,
                    ])
                </span>
            @endif

            <span>
                <strong>{{ $intro['news_help_heading'] }}</strong>
                @if(!empty($intro['news_help_description']))
                    <span>{{ $intro['news_help_description'] }}</span>
                @endif
            </span>
        </div>

        @if($soDT !== '')
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $soDT) }}" class="nx-btn nx-btn--ghost">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 16])
                {{ $soDT }}
            </a>
        @endif

        @if(!empty($intro['news_help_button']))
            <a href="{{ url($nutTuVan) }}" class="nx-btn">{{ $intro['news_help_button'] }}</a>
        @endif
    </div>
@endif
