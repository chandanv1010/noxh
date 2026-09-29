{{--
    Mot the "loai can ho" trong khoi CAC LOAI CAN HO.

    Gia o day la gia CA CAN (don vi luu o cot price_unit), khong phai gia moi
    m2 cua du an - hai con so nay hoan toan khac nhau.

    Tham so: $can (ProjectUnit), $duAn, $intro
--}}
@php
    $dienTich = khoang_so($can->area_from, $can->area_to, ' m²', '');
    // Ba chu so le: gia can ho tinh bang ty, lam tron hai so la lech
    // hang trieu dong.
    $giaCan = khoang_so($can->price_from, $can->price_to, ' ' . $can->price_unit, '', 3);
    $diem = $can->diem;

    // Khong co duong dan rieng thi nut keo xuong form dang ky cua chinh du an.
    $nutUrl = trim((string) $can->url) !== '' ? nx_url($can->url) : '#dang-ky';
@endphp

<article class="nx-pd-can">
    @if($can->image)
        <figure class="nx-pd-can__anh">
            <img src="{{ $can->image }}" alt="{{ $can->name }}" loading="lazy" decoding="async">
        </figure>
    @endif

    <div class="nx-pd-can__chu">
        <h3>{{ $can->name }}</h3>

        @if($dienTich !== '')
            <p>{{ $intro['projectdetail_units_area_label'] ?? 'Diện tích:' }} {{ $dienTich }}</p>
        @endif

        @if($giaCan !== '')
            <p>{{ $intro['projectdetail_units_price_label'] ?? 'Giá dự kiến:' }}
                <strong>{{ $giaCan }}</strong>
            </p>
        @endif

        @if(count($diem))
            <ul>
                @foreach($diem as $d)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16])
                        <span>{{ $d }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ $nutUrl }}" class="nx-btn nx-btn--ghost nx-btn--block nx-pd-can__nut">
            {{ $intro['projectdetail_units_detail_text'] ?? 'XEM CHI TIẾT' }}
        </a>
    </div>
</article>
