{{--
    Mot o dap an cua wizard kiem tra dieu kien (ban ve noxh_image/w-1.jpg):
    hinh tron pastel ben trai, nhan ben phai, dong ghi chu nho mau xam neu co.

    Ca hinh lan mau hinh deu la cot cua rieng tung dap an - quan tri chon o
    man hinh "Dap an dieu kien".
--}}
@php
    [$nen, $net] = $da->mauHinh();
    $hinh = \App\Classes\NoxhIcon::hopLe($da->icon) ? $da->icon : '';
    $ghi = trim((string) $da->note);
    $chon = (string) $daChon === (string) $da->value;
@endphp

<label class="nx-wz-o {{ $chon ? 'da-chon' : '' }}">
    <input type="radio" name="traLoi" value="{{ $da->value }}" @checked($chon)>

    @if($hinh)
        <span class="nx-wz-o__hinh" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
            @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 38])
        </span>
    @endif

    <span class="nx-wz-o__chu">
        <span class="nx-wz-o__ten">{{ $da->label }}</span>
        @if($ghi !== '')
            <small>{{ $ghi }}</small>
        @endif
    </span>
</label>
