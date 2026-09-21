{{--
    The du an dang o vuong, dung o trang chu va cac khoi goi y.

    $duAn la mot dong tu ProjectQuery nen ten/duong dan nam thang tren dong,
    khong phai quan he Eloquent.
--}}
@php
    $url = url('/du-an/' . $duAn->canonical);
    $trangThai = \App\Models\Product::TRANG_THAI_DU_AN[$duAn->status] ?? null;
@endphp

<article class="nx-project">
    <a href="{{ $url }}" class="nx-project__media" title="{{ $duAn->name }}">
        @if(!empty($duAn->image))
            <img src="{{ $duAn->image }}" alt="{{ $duAn->name }}" loading="lazy" decoding="async">
        @endif

        @if($trangThai)
            <span class="nx-badge nx-badge--{{ $duAn->status }} nx-project__badge">{{ $trangThai }}</span>
        @endif
    </a>

    <div class="nx-project__body">
        <h3 class="nx-project__title"><a href="{{ $url }}">{{ $duAn->name }}</a></h3>

        @if(!empty($duAn->province_name))
            <div class="nx-project__place">
                @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 14])
                {{ $duAn->province_name }}
            </div>
        @endif

        <div class="nx-project__price">
            {{ khoang_gia($duAn->price_from, $duAn->price_to) }}
            <small>triệu/m²</small>
        </div>

        <ul class="nx-project__meta">
            <li>
                @include('frontend.noxh.component.icon', ['name' => 'ruler', 'size' => 14])
                Diện tích: <strong>{{ khoang_so($duAn->area_from, $duAn->area_to, ' m²') }}</strong>
            </li>
            @if($duAn->total_units)
                <li>
                    @include('frontend.noxh.component.icon', ['name' => 'layers', 'size' => 14])
                    Số căn: <strong>{{ number_format($duAn->total_units, 0, ',', '.') }} căn</strong>
                </li>
            @endif
            @if($trangThai)
                <li>
                    @include('frontend.noxh.component.icon', ['name' => 'building', 'size' => 14])
                    Tiến độ: <strong>{{ $trangThai }}</strong>
                </li>
            @endif
        </ul>

        <div class="nx-project__actions">
            <a href="{{ $url }}" class="nx-btn nx-btn--ghost nx-btn--sm">Xem chi tiết</a>
        </div>
    </div>
</article>
