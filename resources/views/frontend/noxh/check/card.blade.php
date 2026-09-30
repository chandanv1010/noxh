{{--
    Khoi dap an kieu BA THE LON (ban ve noxh_image/w-4.jpg).

    Moi dap an la mot the doc: hinh tron o tren, ten dam, doan mo ta, o tron
    chon o duoi cung. So luong the KHONG co dinh la ba - quan tri them bot
    duoc o man hinh "Dap an dieu kien", luoi tu chia lai.
--}}
<div class="nx-wz-loai" style="--nx-cot: {{ max(1, $cau->options->count()) }}">
    @foreach($cau->options as $da)
        @php
            [$nen, $net] = $da->mauHinh();
            $anh = trim((string) $da->image);
            $hinh = \App\Classes\NoxhIcon::hopLe($da->icon) ? $da->icon : '';
            $ghi = trim((string) $da->note);
            $chon = (string) $daChon === (string) $da->value;
        @endphp

        <label class="nx-wz-loai__o {{ $chon ? 'da-chon' : '' }}">
            <input type="radio" name="traLoi" value="{{ $da->value }}" @checked($chon)>

            @if($anh !== '')
                <img class="nx-wz-loai__anh" src="{{ $anh }}" alt="" loading="lazy">
            @elseif($hinh)
                <span class="nx-wz-loai__hinh" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
                    @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 44])
                </span>
            @endif

            <strong class="nx-wz-loai__ten">{{ $da->label }}</strong>

            @if($ghi !== '')
                <small class="nx-wz-loai__mo">{{ $ghi }}</small>
            @endif

            <span class="nx-wz-loai__tron" aria-hidden="true"></span>
        </label>
    @endforeach
</div>
