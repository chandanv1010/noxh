{{--
    Anh dai dien mot nguoi.

    Co anh thi dung anh; chua co thi ve mot dia tron mang hai chu cai dau
    cua ho ten - cach nay ro rang hon la sau nguoi cung mot hinh bong nguoi
    giong het nhau, va khong can tai them file nao.

    Tham so:
      $ten   - ho ten, bat buoc
      $anh   - duong dan anh, co the de trong
      $co    - duong kinh tinh bang diem anh (mac dinh 48)
--}}
@php
    $co = $co ?? 48;
    $anh = trim((string) ($anh ?? ''));
@endphp

@if($anh !== '')
    <img src="{{ nx_anh($anh, 'avatar') }}" alt="{{ $ten }}" loading="lazy" decoding="async">
@else
    <span class="nx-avatar-chu" aria-hidden="true"
          style="--nx-avatar-mau: {{ nx_mau_tu_ten($ten) }}; font-size: {{ round($co * 0.38) }}px">
        {{ nx_chu_dau($ten) }}
    </span>
    <span class="nx-an-di">{{ $ten }}</span>
@endif
