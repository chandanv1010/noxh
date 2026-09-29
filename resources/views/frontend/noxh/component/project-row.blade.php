{{--
    Mot dong du an trong trang danh sach.

    Tham so:
      $d - dong du an lay tu ProjectQuery (can name, canonical, image,
           status, province_name, price_from/to, area_from/to, total_units,
           description, updated_at).
--}}
@php
    $url = url('/du-an/' . $d->canonical);
    $tt = \App\Models\Product::TRANG_THAI_DU_AN[$d->status] ?? null;

    // Ba o thong so luon du ba cot du lieu co thieu: ban thiet ke xep chung
    // thanh luoi ba cot deu nhau, bo bot mot o la hai o con lai gian ra va
    // ca cot the bi lech so voi cac the khac.
    $thongSo = [
        ['icon' => 'area', 'nhan' => 'Diện tích', 'gia' => khoang_so($d->area_from, $d->area_to, ' m²')],
        ['icon' => 'units', 'nhan' => 'Số căn', 'gia' => $d->total_units
            ? '~ ' . number_format($d->total_units, 0, ',', '.') . ' căn'
            : null],
        ['icon' => 'schedule', 'nhan' => 'Tiến độ', 'gia' => $tt],
    ];
@endphp

<article class="nx-du-an">
    <a href="{{ $url }}" class="nx-du-an__anh" title="{{ $d->name }}">
        <img src="{{ nx_anh($d->image ?? null, 'du-an') }}" alt="{{ $d->name }}" loading="lazy" decoding="async">
        @if($tt)
            <span class="nx-badge nx-badge--{{ $d->status }} nx-du-an__nhan">{{ $tt }}</span>
        @endif
    </a>

    <div class="nx-du-an__than">
        <div class="nx-du-an__dau">
            <div>
                <h2 class="nx-du-an__ten"><a href="{{ $url }}">{{ $d->name }}</a></h2>
                @if($d->province_name)
                    <p class="nx-du-an__noi">
                        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 14])
                        {{ nx_ten_dia_gioi_ngan($d->province_name) }}
                    </p>
                @endif
            </div>

            <p class="nx-du-an__gia">
                {{ khoang_gia($d->price_from, $d->price_to) }}
                <small>triệu/m²</small>
            </p>
        </div>

        @if($d->description)
            <p class="nx-du-an__mota">{{ \Illuminate\Support\Str::words(strip_tags($d->description), 24, '…') }}</p>
        @endif

        <div class="nx-du-an__thong-so">
            @foreach($thongSo as $o)
                <div>
                    <span class="nx-du-an__nhan-o">
                        @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 14])
                        {{ $o['nhan'] }}
                    </span>
                    <strong>{{ $o['gia'] ?: '—' }}</strong>
                </div>
            @endforeach
        </div>

        <div class="nx-du-an__chan">
            <span class="nx-du-an__ngay">
                Cập nhật: {{ \Illuminate\Support\Carbon::parse($d->updated_at)->format('d/m/Y') }}
            </span>

            <span class="nx-du-an__nut">
                <a href="{{ $url }}" class="nx-btn nx-btn--ghost nx-btn--sm">Xem chi tiết</a>
                <a href="{{ $url }}#dang-ky" class="nx-btn nx-btn--sm">Đăng ký tư vấn</a>
            </span>
        </div>
    </div>
</article>
