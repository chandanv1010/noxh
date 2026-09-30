{{--
    Thanh buoc cua wizard.

    Dung chung cho ca hai kieu the: buoc dau in tren mot dai ngoai the
    (ban ve w-1.jpg), cac buoc sau in trong the (ban ve w-2.jpg). Buoc DA XONG
    ve vong tron xanh la kem dau tich, buoc dang lam ve vong tron xanh duong.
--}}
<ol class="{{ $trong ? '' : 'nx__container' }} nx-wz-buoc__ds">
    @foreach($cauHoi as $i => $ch)
        @php $so = $i + 1; @endphp
        <li class="nx-wz-buoc__o
            {{ $so === $buoc ? 'dang-lam' : '' }}
            {{ $so < $buoc ? 'da-xong' : '' }}
            {{ $so <= $buoc && !$loop->last ? 'noi-xanh' : '' }}">
            <span class="nx-wz-buoc__so">
                @if($so < $buoc)
                    @include('frontend.noxh.component.icon', ['name' => 'check', 'size' => 18])
                @else
                    {{ $so }}
                @endif
            </span>
            <span class="nx-wz-buoc__ten">{{ $ch->tenBuoc() }}</span>
        </li>
    @endforeach
</ol>
