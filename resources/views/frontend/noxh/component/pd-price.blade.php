{{--
    The gia o goc phai dau trang chi tiet du an.

    Bon o thong so (Quy mo / So can ho / Loai hinh / Ban giao) va bon o diem
    nhan ben duoi deu do controller don san: nhan tu Cau hinh chung, con so tu
    du an, diem nhan tu bang project_highlights hoac bon o mac dinh.

    Tham so: $duAn, $thongSo, $diemNhan, $intro
--}}
@php
    $gia = khoang_so($duAn->price_from, $duAn->price_to, ' triệu/m²', '');

    $camKet = array_values(array_filter([
        $intro['projectdetail_trust_1'] ?? null,
        $intro['projectdetail_trust_2'] ?? null,
        $intro['projectdetail_trust_3'] ?? null,
    ]));
@endphp

<section class="nx-pd-gia">
    <p class="nx-pd-gia__nhan">{{ $intro['projectdetail_price_label'] ?? 'Giá bán dự kiến' }}</p>

    <p class="nx-pd-gia__so{{ $gia === '' ? ' la-trong' : '' }}">
        {{ $gia !== '' ? $gia : ($intro['projectdetail_price_empty'] ?? 'Đang cập nhật') }}
    </p>

    @if($gia !== '' && !empty($intro['projectdetail_price_note']))
        <p class="nx-pd-gia__ghi">{{ $intro['projectdetail_price_note'] }}</p>
    @endif

    @if(count($thongSo) || count($diemNhan))
        <div class="nx-pd-gia__o">
            @foreach(array_merge($thongSo, $diemNhan) as $o)
                <div class="nx-pd-o">
                    @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 24])
                    <span>
                        <span class="nx-pd-o__nhan">{{ $o['nhan'] }}</span>
                        @if(!empty($o['gia']))
                            <strong>{{ $o['gia'] }}</strong>
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
    @endif

    <a href="#dang-ky" class="nx-btn nx-btn--block nx-pd-gia__nut">
        {{ $intro['projectdetail_price_button'] ?? 'ĐĂNG KÝ TƯ VẤN NGAY' }}
        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 18])
    </a>

    @if(count($camKet))
        <p class="nx-pd-gia__cam">
            @include('frontend.noxh.component.icon', [
                'name' => $intro['projectdetail_trust_icon'] ?? 'shield-check', 'size' => 16,
            ])
            @foreach($camKet as $i => $c)
                @if($i)<span aria-hidden="true">|</span>@endif
                <span>{{ $c }}</span>
            @endforeach
        </p>
    @endif
</section>
