{{--
    The du an dang o vuong, dung o trang chu va cac khoi goi y.

    $duAn la mot dong tu ProjectQuery nen ten/duong dan nam thang tren dong,
    khong phai quan he Eloquent.
--}}
@php
    $url = url('/du-an/' . $duAn->canonical);
    $trangThai = \App\Models\Product::TRANG_THAI_DU_AN[$duAn->status] ?? null;

    // Hai o thong tin duoi gia: so can ho va moc ban giao - dung nhu ban
    // thiet ke. O nao khong co so lieu thi bo han, khong in "dang cap nhat".
    $oTin = [];

    if (!empty($duAn->total_units)) {
        $oTin[] = ['icon' => 'grid', 'text' => number_format($duAn->total_units, 0, ',', '.') . ' căn hộ'];
    }

    if (!empty($duAn->timeline_label)) {
        $oTin[] = ['icon' => 'calendar', 'text' => $duAn->timeline_label];
    }
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

        @php
            // Ban thiet ke ghi "phuong/xa, tinh". Ten rut gon (bo "Tinh",
            // "Thanh pho", "Phuong") cho vua mot dong tren the.
            $noiO = array_filter([
                nx_ten_dia_gioi_ngan($duAn->ward_name ?? ''),
                nx_ten_dia_gioi_ngan($duAn->province_name ?? ''),
            ]);
        @endphp
        @if(count($noiO))
            <div class="nx-project__place">
                @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 15])
                {{ implode(', ', $noiO) }}
            </div>
        @endif

        <div class="nx-project__price">
            {{ khoang_gia($duAn->price_from, $duAn->price_to) }}
            <small>triệu/m²</small>
        </div>

        @if(count($oTin))
            <ul class="nx-project__meta">
                @foreach($oTin as $o)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 16])
                        {{ $o['text'] }}
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="nx-project__actions">
            <a href="{{ $url }}" class="nx-btn nx-btn--ghost nx-btn--sm nx-btn--block">
                XEM CHI TIẾT
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
            </a>
        </div>
    </div>
</article>
