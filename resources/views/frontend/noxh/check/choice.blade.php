{{--
    Mot dong chon trong tam tinh huong (ban ve noxh_image/w-2.jpg).

    Dong mang ket luan "Dat" ve vien xanh la va chu dam - ban ve lam vay de
    nguoi doc nhin phat la biet nguong nao con trong quy dinh.
--}}
@php
    $chon = (string) $daChon === (string) $da->value;
@endphp

<label class="nx-wz-chon {{ $da->verdict === 'pass' ? 'dat' : '' }} {{ $chon ? 'da-chon' : '' }}">
    <input type="radio" name="traLoi" value="{{ $da->value }}" @checked($chon)>
    <span class="nx-wz-chon__tron" aria-hidden="true"></span>
    <span class="nx-wz-chon__chu">
        {{ $da->label }}
        @if(trim((string) $da->note) !== '')
            <small>{{ $da->note }}</small>
        @endif
    </span>
</label>
