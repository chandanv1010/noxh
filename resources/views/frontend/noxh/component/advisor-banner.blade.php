{{--
    Banner "Can tu van du an phu hop?" o duoi bo loc, cot trai.

    Hai cach dat anh, quan tri chon mot:

      1. "Anh nen ca khoi" (banner_bg) - mot anh NGANG phu kin banner. Dung
         khi ben thiet ke giao ca tam anh da ghep san nguoi + nen. Chu van
         nam de len tren, phia sau chu co mot lop mo de con doc duoc.

      2. "Anh nguoi tu van" (banner_image) - anh DOC da tach nen, dat sat mep
         phai tren nen xanh chuyen mau.

    Khong co anh nao thi dung hinh ve san
    public/images/noxh/tu-van-vien.svg, de banner khong bi trong mot nua.

    Nut: co duong dan rieng thi theo duong dan do, khong thi goi vao Hotline.
--}}
@php
    $diem = array_values(array_filter([
        $intro['projectaside_banner_point_1'] ?? null,
        $intro['projectaside_banner_point_2'] ?? null,
        $intro['projectaside_banner_point_3'] ?? null,
    ]));

    $anhNen = trim((string) ($intro['projectaside_banner_bg'] ?? ''));

    // Chua co anh nguoi nao thi lay hinh ve mac dinh - de trong thi banner
    // trong trong hon han ban thiet ke.
    $anhNguoi = $anhNen === ''
        ? nx_anh(
            trim((string) ($intro['projectaside_banner_image'] ?? '')) ?: ($chuyenGia->image ?? ''),
            'tu-van'
        )
        : null;

    $duongDan = trim((string) ($intro['projectaside_banner_url'] ?? ''));
    $hotline = nx_hotline_dau($system['contact_hotline'] ?? '');

    if ($duongDan !== '') {
        $nutUrl = nx_url($duongDan);
    } elseif ($hotline !== '') {
        $nutUrl = 'tel:' . preg_replace('/[^0-9+]/', '', $hotline);
    } else {
        $nutUrl = url('/lien-he');
    }

    $nut = $intro['projectaside_banner_button'] ?? 'LIÊN HỆ NGAY';
@endphp

<section class="nx-tu-van-banner {{ $anhNen !== '' ? 'co-nen' : 'co-anh' }}"
         @if($anhNen !== '') style="--nx-nen: url('{{ e($anhNen) }}')" @endif>
    <div class="nx-tu-van-banner__chu">
        <h2>{{ $intro['projectaside_banner_heading'] ?? 'Cần tư vấn dự án phù hợp?' }}</h2>

        @if($diem)
            <ul>
                @foreach($diem as $d)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16])
                        <span>{{ $d }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if($nut)
            <a href="{{ $nutUrl }}" class="nx-btn nx-btn--sm nx-tu-van-banner__nut">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                {{ $nut }}
            </a>
        @endif
    </div>

    @if($anhNguoi)
        <img class="nx-tu-van-banner__anh" src="{{ $anhNguoi }}"
             alt="{{ $chuyenGia->name ?? 'Chuyên viên tư vấn' }}" loading="lazy" decoding="async">
    @endif
</section>
