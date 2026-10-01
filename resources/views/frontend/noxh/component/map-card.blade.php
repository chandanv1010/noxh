{{--
    Mot the du an trong cot trai trang Ban do.

    The nay va the hien ra khi bam vao ghim tren ban do la cung mot bo du
    lieu ($diem, do ProjectController::diemBanDo dung) - chu in o hai noi
    phai giong nhau, nen ca hai cung doc tu day chu khong tu tinh lai.

    Ca the KHONG phai mot the <a> lon: ben trong con mot nut "Xem tren ban
    do", ma long nut vao trong lien ket thi ban phim khong lan duoc giua hai
    thu. Anh va ten moi la lien ket, phan con lai chi la chu.

    Tham so:
      $d - mot phan tu cua $diem
      $o - ham doc chu cua trang (dong trong map.blade.php)
--}}
<article class="nx-bd-the" data-nx-bando-the="{{ $d['id'] }}">
    <a href="{{ $d['url'] }}" class="nx-bd-the__anh" tabindex="-1" aria-hidden="true">
        <img src="{{ $d['anh'] }}" alt="" loading="lazy" decoding="async">
        @if($d['nhanTrangThai'])
            <span class="nx-badge nx-badge--{{ $d['trangThai'] }}">{{ $d['nhanTrangThai'] }}</span>
        @endif
    </a>

    <div class="nx-bd-the__than">
        <h3 class="nx-bd-the__ten"><a href="{{ $d['url'] }}">{{ $d['ten'] }}</a></h3>

        @if($d['noi'])
            <p class="nx-bd-the__noi">
                @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 13])
                {{ $d['noi'] }}
            </p>
        @endif

        <p class="nx-bd-the__gia">
            {{ $d['gia'] }} <small>{{ $o('card_price_unit', 'triệu/m²') }}</small>
        </p>

        <div class="nx-bd-the__chan">
            <span class="nx-bd-the__so">
                <span>
                    @include('frontend.noxh.component.icon', ['name' => 'area', 'size' => 13])
                    {{ $d['dienTich'] }}
                </span>
                @if($d['soCan'])
                    <span>
                        @include('frontend.noxh.component.icon', ['name' => 'units', 'size' => 13])
                        {{ $d['soCan'] }}
                    </span>
                @endif
            </span>

            {{-- Khong co JS thi nut nay khong lam duoc gi, nen de JS tu bat
                 ra - an nut bam vao khong phan ung con kho chiu hon la
                 khong co nut. --}}
            <button type="button" class="nx-bd-the__xem" data-nx-bando-xem hidden>
                @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 13])
                {{ $o('card_show_text', 'Xem trên bản đồ') }}
            </button>
        </div>
    </div>
</article>
