{{--
    Khoi "Ban do du an" o cot phai trang danh sach.

    Hinh ban do, danh sach tinh kem so du an, va nut xem them. Ca ba deu dan
    sang /du-an/ban-do hoac ve chinh trang danh sach da loc theo tinh.

    Tham so:
      $ghimBanDo  - ghim da chieu san (ProjectController::ghimBanDo)
      $tinhCoDuAn - cac tinh hien trong luoi the
--}}
@php
    $banDo = url('/du-an/ban-do');
@endphp

<section class="nx-panel nx-ban-do">
    <h2 class="nx-panel__title">
        {{ $intro['projectaside_map_heading'] ?? 'Bản đồ dự án' }}
        <a href="{{ $banDo }}">
            {{ $intro['projectaside_map_all_text'] ?? 'Xem tất cả' }}
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 13])
        </a>
    </h2>

    <a href="{{ $banDo }}" class="nx-ban-do__hinh" aria-label="Mở bản đồ dự án toàn quốc">
        @include('frontend.noxh.component.vn-map', ['diem' => $ghimBanDo])
    </a>

    @if(count($tinhCoDuAn))
        <div class="nx-ban-do__tinh">
            @foreach($tinhCoDuAn as $t)
                {{-- Sang trang ban do chu khong loc tai cho: the tinh o day la
                     loi vao ban do khu vuc, con loc danh sach da co o bo loc
                     ben cot trai. --}}
                <a href="{{ url('/du-an/ban-do?province_code=' . $t->province_code) }}"
                   class="{{ request('province_code') == $t->province_code ? 'is-chon' : '' }}">
                    {{ nx_ten_dia_gioi_ngan($t->province_name) }}
                    <span>({{ $t->so_du_an }})</span>
                </a>
            @endforeach
        </div>
    @endif

    <a href="{{ $banDo }}" class="nx-ban-do__them">
        {{ $intro['projectaside_map_more_text'] ?? 'Xem thêm tỉnh thành' }}
        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
    </a>
</section>
