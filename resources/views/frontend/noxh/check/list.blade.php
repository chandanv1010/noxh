{{--
    Khoi dap an kieu DANH SACH DOC (ban ve noxh_image/w-3.jpg).

    Moi dap an la mot hang: o tron chon o mep trai, hinh vuong bo tron, roi
    ten dam va doan mo ta hai dong. Hang dang chon doi vien xanh va nen xanh
    nhat.
--}}
<div class="nx-wz-hang">
    @foreach($cau->options as $da)
        @php
            [$nen, $net] = $da->mauHinh();
            $anh = trim((string) $da->image);
            $hinh = \App\Classes\NoxhIcon::hopLe($da->icon) ? $da->icon : '';
            $ghi = trim((string) $da->note);
            $chon = (string) $daChon === (string) $da->value;
        @endphp

        <label class="nx-wz-hang__o {{ $chon ? 'da-chon' : '' }}">
            <input type="radio" name="traLoi" value="{{ $da->value }}" @checked($chon)>
            <span class="nx-wz-hang__tron" aria-hidden="true"></span>

            @if($anh !== '')
                <img class="nx-wz-hang__anh" src="{{ $anh }}" alt="" loading="lazy">
            @elseif($hinh)
                <span class="nx-wz-hang__hinh" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
                    @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 34])
                </span>
            @endif

            <span class="nx-wz-hang__chu">
                <strong>{{ $da->label }}</strong>
                @if($ghi !== '')
                    <small>{{ $ghi }}</small>
                @endif
            </span>
        </label>
    @endforeach
</div>
