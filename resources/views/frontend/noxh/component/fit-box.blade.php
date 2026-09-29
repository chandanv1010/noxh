{{--
    Khoi "Co du an phu hop voi ban?" - o duoi cung cot phai, nen kem vang.

    Chu, hinh va duong dan lay tu module Gioi thieu, nhom "Khoi 4b".
--}}
@php
    $diem = array_values(array_filter([
        $intro['projectaside_fit_point_1'] ?? null,
        $intro['projectaside_fit_point_2'] ?? null,
        $intro['projectaside_fit_point_3'] ?? null,
    ]));

    $hinh = $intro['projectaside_fit_icon'] ?? '';
    $hinh = \App\Classes\NoxhIcon::hopLe($hinh) ? $hinh : 'bulb-rays';

    $nut = $intro['projectaside_fit_button'] ?? 'ĐĂNG KÝ TƯ VẤN MIỄN PHÍ';
    $duongDan = trim((string) ($intro['projectaside_fit_url'] ?? 'kiem-tra-dieu-kien'));
@endphp

<section class="nx-phu-hop">
    <h2 class="nx-phu-hop__dau">
        <span class="nx-phu-hop__hinh">
            @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 34])
        </span>
        {{ $intro['projectaside_fit_heading'] ?? 'Có dự án phù hợp với bạn?' }}
    </h2>

    @if($diem)
        <ul class="nx-phu-hop__ds">
            @foreach($diem as $d)
                <li>
                    @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17])
                    <span>{{ $d }}</span>
                </li>
            @endforeach
        </ul>
    @endif

    @if($nut)
        <a href="{{ nx_url($duongDan) }}" class="nx-btn nx-btn--vang nx-btn--block">
            {{ $nut }}
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 16])
        </a>
    @endif
</section>
