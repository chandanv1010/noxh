{{--
    Thanh buoc cua wizard.

    Dung chung cho ca hai kieu the: buoc dau in tren mot dai ngoai the
    (ban ve w-1.jpg), cac buoc sau in trong mot the rieng (w-2..w-5). Buoc DA
    XONG ve vong tron xanh la kem dau tich, buoc dang lam ve vong tron xanh
    duong, buoc BI BO QUA (dap an truoc do da du ket luan) ve mo di.
--}}
<ol class="{{ $trong ? '' : 'nx__container' }} nx-wz-buoc__ds">
    @foreach($cauHoi as $i => $ch)
        @php
            $so = $i + 1;
            $diQua = in_array($so, $duongDi, true);
        @endphp
        <li class="nx-wz-buoc__o
            {{ $so === $buoc ? 'dang-lam' : '' }}
            {{ $so < $buoc && $diQua ? 'da-xong' : '' }}
            {{ !$diQua ? 'bo-qua' : '' }}
            {{ $so <= $buoc && $diQua && !$loop->last ? 'noi-xanh' : '' }}">
            <span class="nx-wz-buoc__so">
                @if($so < $buoc && $diQua)
                    @include('frontend.noxh.component.icon', ['name' => 'check', 'size' => 18])
                @else
                    {{ $so }}
                @endif
            </span>
            <span class="nx-wz-buoc__ten">{{ $ch->tenBuoc() }}</span>
        </li>
    @endforeach
</ol>
