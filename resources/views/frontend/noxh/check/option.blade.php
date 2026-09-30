{{--
    Mot o dap an cua wizard kiem tra dieu kien (ban ve noxh_image/w-1.jpg):
    hinh tron ben trai, nhan ben phai, dong ghi chu nho mau xam neu co.

    Hinh uu tien ANH cua dap an - ban ve dung tranh minh hoa nhieu mau, cat
    thang tu ban ve ra (tools/tach-anh-ban-ve.py). Dap an chua co anh thi ve
    hinh net trong vong tron mau pastel; ca hai deu la cot cua rieng tung dap
    an, quan tri chon o man hinh "Dap an dieu kien".
--}}
@php
    [$nen, $net] = $da->mauHinh();
    $anh = trim((string) $da->image);
    $hinh = \App\Classes\NoxhIcon::hopLe($da->icon) ? $da->icon : '';
    $ghi = trim((string) $da->note);
    $chon = (string) $daChon === (string) $da->value;
@endphp

<label class="nx-wz-o {{ $chon ? 'da-chon' : '' }}">
    <input type="radio" name="traLoi" value="{{ $da->value }}" @checked($chon)>

    @if($anh !== '')
        <img class="nx-wz-o__anh" src="{{ $anh }}" alt="" loading="lazy">
    @elseif($hinh)
        <span class="nx-wz-o__hinh" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
            @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 40])
        </span>
    @endif

    <span class="nx-wz-o__chu">
        <span class="nx-wz-o__ten">{{ $da->label }}</span>
        @if($ghi !== '')
            <small>{{ $ghi }}</small>
        @endif
    </span>
</label>
