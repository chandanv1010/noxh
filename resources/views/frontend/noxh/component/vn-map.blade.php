{{--
    Ban do Viet Nam co cham ghim du an.

    Hinh la SVG dan thang, khong goi dich vu ban do nao: khoi nay chi la anh
    dan huong o cot phai, bam vao thi sang trang /du-an/ban-do - keo ca mot
    thu vien ban do ve chi de ve mot hinh tinh la khong dang.

    Duong bien duoc don gian hoa va chieu bang cung phep tinh voi ghim
    (nx_ban_do_diem), nen ghim luon roi dung vao trong dat lien. Khung toa do
    khai bao o nx_ban_do_khung() - sua hinh thi phai sua ca ham do.

    Tham so:
      $diem      - mang cac ['x','y','ten','so','url'] da chieu san.
      $nhan      - true thi hien ten tinh canh ghim (dung o trang ban do).
--}}
@php
    $khung = nx_ban_do_khung();
@endphp

<svg class="nx-vnmap" viewBox="0 0 {{ $khung['rong'] }} {{ $khung['cao'] }}"
     xmlns="http://www.w3.org/2000/svg" role="img"
     aria-label="Bản đồ dự án nhà ở xã hội trên cả nước">
    <path class="nx-vnmap__dat" d="M31.2 122.7 L87.5 85.9 L157.5 81.8 L258.7 81.8 L340 49.1 L427.5 24.5 L493.7 67.5 L581.2 69.6 L602.5 81.8 L640 112.5 L685 204.6 L768.7 209.7 L650 271.1 L612.5 291.5 L587.5 342.7 L537.5 373.3 L502.5 409.2 L487.5 450.1 L482.5 480.8 L502.5 511.4 L527.5 542.1 L575 577.9 L590 624 L652.5 670 L715 716 L790 767.2 L820 818.3 L865 859.2 L915 951.3 L925 1002.4 L920 1074 L945 1114.9 L915 1166.1 L925 1217.2 L875 1258.1 L825 1288.8 L737.5 1329.7 L650 1350.2 L607.5 1360.4 L615 1391.1 L600 1421.8 L575 1442.3 L537.5 1452.5 L500 1472.9 L425 1534.3 L368.7 1528.2 L362.5 1472.9 L387.5 1421.8 L375 1370.7 L325 1350.2 L387.5 1299.1 L493.7 1288.8 L537.5 1217.2 L565 1190.6 L612.5 1191.7 L662.5 1155.8 L707.5 1125.2 L700 1084.2 L687.5 1022.9 L675 971.7 L707.5 920.6 L682.5 869.4 L662.5 818.3 L625 756.9 L575 716 L537.5 675.1 L500 624 L450 572.8 L412.5 531.9 L362.5 491 L337.5 470.5 L300 429.6 L262.5 398.9 L287.5 347.8 L337.5 296.6 L312.5 265.9 L250 245.5 L162.5 204.6 L125 194.3 L112.5 163.7 Z"/>

    @foreach($diem ?? [] as $d)
        <g class="nx-vnmap__ghim{{ !empty($d['sang']) ? ' is-sang' : '' }}"
           transform="translate({{ $d['x'] }} {{ $d['y'] }})">
            <title>{{ $d['ten'] }}@if(!empty($d['so'])) ({{ $d['so'] }} dự án)@endif</title>
            {{-- Ghim ve quanh goc toa do roi keo len 34 don vi: mui ghim
                 phai cham dung diem, khong phai tam hinh tron. --}}
            <path d="M0 0 c-15 -18 -24 -29 -24 -40 a24 24 0 1 1 48 0 c0 11 -9 22 -24 40 Z"
                  transform="translate(0 -2)"/>
            <circle cx="0" cy="-42" r="9.5" fill="#fff"/>
        </g>
    @endforeach
</svg>
