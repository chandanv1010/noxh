@extends('frontend.noxh.layout')

{{--
    Trang Ban do du an - /du-an/ban-do.

    Hai cot: danh sach du an ben trai, ban do that ben phai. Moi du an mot
    ghim dat dung toa do; bam vao ghim ra the thong tin, bam vao the thi sang
    trang chi tiet du an.

    Phan ve ban do nam o resources/js/noxh-map.js - trang nay chi lo giao ra
    du lieu: danh sach ghim (the <script type="application/json">) va mo cai
    dat (cac thuoc tinh data- cua khung ban do).

    Toan bo chu lay tu module Gioi thieu, nhom "Khối 4c".
--}}

@push('head')
    @vite('resources/js/noxh-map.js')
@endpush

@php
    $o = fn ($ten, $du = '') => trim((string) ($intro['projectmap_' . $ten] ?? '')) ?: $du;

    $tenBanDo = $o('heading', 'Bản đồ dự án');
    $tenTinh = $tinhDangXem ? nx_ten_dia_gioi_ngan($tinhDangXem->name) : null;
    $tenXa = $xaDangXem ? nx_ten_dia_gioi_ngan($xaDangXem->ward_name) : null;

    // Noi dang xem: dung ca o tieu de danh sach lan o duong dan.
    $noi = $tenXa && $tenTinh ? $tenXa . ', ' . $tenTinh : ($tenTinh ?: '');

    $crumbs = ['Dự án' => url('/du-an'), $tenBanDo => $tenTinh ? url('/du-an/ban-do') : ''];

    if ($tenTinh) {
        $crumbs[$tenTinh] = '';
    }

    $tieuDeDs = strtr($o('list_heading', '{so} dự án trên bản đồ {noi}'), [
        '{so}' => number_format(count($diem), 0, ',', '.'),
        '{noi}' => $noi ? 'tại ' . $noi : 'trên cả nước',
    ]);
@endphp

@section('content')

@include('frontend.noxh.component.page-head', [
    'crumbs' => $crumbs,
    'tieuDe' => $tenTinh ? 'Bản đồ dự án nhà ở xã hội tại ' . $tenTinh : $tenBanDo,
    'moTa' => $o('description'),
    'soLieu' => $soLieuDau,
])

<div class="nx-bando">
    <div class="nx__container">
        {{-- Bo loc gui bang GET: duong dan mang day du bo loc nen chia se
             duoc, va trang van chay khi trinh duyet tat JS. --}}
        <form class="nx-bando__loc" method="get" action="{{ url('/du-an/ban-do') }}" data-nx-bando-loc>
            <div class="nx-bando__o">
                <label for="bd-tinh">{{ $o('filter_province_label', 'Tỉnh / Thành phố') }}</label>
                <select name="province_code" id="bd-tinh" data-nx-bando-tinh>
                    <option value="">{{ $o('filter_province_all', 'Toàn quốc') }}</option>
                    @foreach($tinhThanh as $t)
                        <option value="{{ $t->province_code }}" @selected($loc['province_code'] === $t->province_code)>
                            {{ nx_ten_dia_gioi_ngan($t->province_name) }}@if($t->so_du_an) ({{ $t->so_du_an }})@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="nx-bando__o">
                <label for="bd-xa">{{ $o('filter_ward_label', 'Phường / Xã') }}</label>
                {{-- Chua chon tinh thi khoa o nay lai: do ca 3321 phuong/xa
                     ra thi cuon mai khong het ma phan lon chon vao se ra
                     trang trong. --}}
                <select name="ward_code" id="bd-xa" @disabled(!$loc['province_code'])>
                    <option value="">
                        {{ $loc['province_code']
                            ? $o('filter_ward_all', 'Tất cả phường / xã')
                            : $o('filter_ward_empty', 'Chọn tỉnh/thành trước') }}
                    </option>
                    @foreach($xaCoDuAn as $x)
                        <option value="{{ $x->ward_code }}" @selected($loc['ward_code'] === $x->ward_code)>
                            {{ nx_ten_dia_gioi_ngan($x->ward_name) }} ({{ $x->so_du_an }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="nx-bando__o">
                <label for="bd-tt">{{ $o('filter_status_label', 'Trạng thái') }}</label>
                <select name="status[]" id="bd-tt">
                    <option value="">{{ $o('filter_status_all', 'Tất cả trạng thái') }}</option>
                    @foreach($trangThai as $ma => $ten)
                        <option value="{{ $ma }}" @selected(in_array($ma, $loc['status'], true))>{{ $ten }}</option>
                    @endforeach
                </select>
            </div>

            <div class="nx-bando__o nx-bando__o--rong">
                <label for="bd-tu">{{ $o('filter_keyword_label', 'Từ khoá') }}</label>
                <input type="search" name="tu-khoa" id="bd-tu" value="{{ $loc['keyword'] }}"
                       placeholder="{{ $o('filter_keyword_placeholder', 'Tên dự án, địa chỉ…') }}">
            </div>

            <div class="nx-bando__o nx-bando__o--nut">
                <button type="submit" class="nx-btn nx-btn--sm">
                    @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 16])
                    {{ $o('filter_button', 'Tìm trên bản đồ') }}
                </button>

                @if($loc['province_code'] || $loc['ward_code'] || $loc['keyword'] || count($loc['status']))
                    <a href="{{ url('/du-an/ban-do') }}" class="nx-bando__xoa">
                        {{ $o('filter_clear', 'Xoá lọc') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="nx__container nx-bando__khung">
        <div class="nx-bando__trai">
            <h2 class="nx-bando__tieude">{{ $tieuDeDs }}</h2>

            @if($thieuToaDo)
                <p class="nx-bando__thieu">
                    @include('frontend.noxh.component.icon', ['name' => 'info', 'size' => 15])
                    {{ strtr($o('list_missing', 'Còn {so} dự án chưa có toạ độ nên chưa hiện trên bản đồ.'), [
                        '{so}' => number_format($thieuToaDo, 0, ',', '.'),
                    ]) }}
                </p>
            @endif

            @if(count($diem))
                <div class="nx-bando__ds" data-nx-bando-ds>
                    @foreach($diem as $d)
                        @include('frontend.noxh.component.map-card', ['d' => $d, 'o' => $o])
                    @endforeach
                </div>
            @else
                <p class="nx-empty">{{ $o('list_empty', 'Không có dự án nào khớp với bộ lọc.') }}</p>
            @endif

            <a href="{{ url('/du-an') }}" class="nx-btn nx-btn--ghost nx-btn--sm nx-bando__ve">
                @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 15])
                {{ $o('back_text', 'Về danh sách dự án') }}
            </a>
        </div>

        <div class="nx-bando__phai">
            {{-- JS doc het cai dat o day. Thieu JS (hoac chan JS) thi o nay
                 van la mot o trong co vien, khong vo bo cuc trang. --}}
            <div class="nx-bando__hinh"
                 data-nx-bando
                 data-nen="{{ $caiDatBanDo['nen'] }}"
                 data-key="{{ $caiDatBanDo['key'] }}"
                 data-anh-nen="{{ $caiDatBanDo['anhNen'] }}"
                 data-nguon="{{ $caiDatBanDo['nguon'] }}"
                 data-lat="{{ $khungBanDo['lat'] }}"
                 data-lng="{{ $khungBanDo['lng'] }}"
                 data-zoom="{{ $khungBanDo['zoom'] }}"
                 data-chi-tiet="{{ $o('card_detail_text', 'Xem chi tiết') }}"
                 data-don-vi="{{ $o('card_price_unit', 'triệu/m²') }}"
                 data-nhan-dt="{{ $o('card_area_label', 'Diện tích') }}"
                 data-nhan-can="{{ $o('card_unit_label', 'Số căn') }}"
                 data-nhom="{{ $o('card_group_text', '{so} dự án') }}"
                 role="application"
                 aria-label="{{ $tenBanDo }}"></div>

            @if($o('note'))
                <p class="nx-bando__ghichu">{{ $o('note') }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Danh sach ghim. De trong the application/json chu khong nhet vao thuoc
     tinh data-: du lieu nay dai, va trinh duyet khong phai thoat chuoi hai
     lan nhu khi nhet vao HTML. --}}
<script type="application/json" data-nx-bando-diem>@json($diem, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE)</script>
@endsection
