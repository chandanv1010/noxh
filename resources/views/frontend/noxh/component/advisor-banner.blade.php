{{--
    Banner "Can tu van du an phu hop?" o duoi bo loc, cot trai.

    Chu va anh lay tu module Gioi thieu (nhom "Khoi 4b"). Anh de trong thi
    lay anh cua chuyen gia mac dinh ($chuyenGia do NoxhComposer cap); van
    trong nua thi banner chi co chu, khong de o anh rong.

    Nut: co duong dan rieng thi theo duong dan do, khong thi goi vao Hotline.
--}}
@php
    $diem = array_values(array_filter([
        $intro['projectaside_banner_point_1'] ?? null,
        $intro['projectaside_banner_point_2'] ?? null,
        $intro['projectaside_banner_point_3'] ?? null,
    ]));

    // Chua co anh nao thi lay hinh ve mac dinh - de trong thi banner
    // trong trong hon han ban thiet ke.
    $anh = nx_anh(
        trim((string) ($intro['projectaside_banner_image'] ?? '')) ?: ($chuyenGia->image ?? ''),
        'tu-van'
    );

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

<section class="nx-tu-van-banner co-anh">
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

    <img class="nx-tu-van-banner__anh" src="{{ $anh }}"
         alt="{{ $chuyenGia->name ?? 'Chuyên viên tư vấn' }}" loading="lazy" decoding="async">
</section>
