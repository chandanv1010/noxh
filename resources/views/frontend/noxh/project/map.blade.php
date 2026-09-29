@extends('frontend.noxh.layout')

@section('content')
@php
    $tenBanDo = $intro['projectaside_map_heading'] ?? 'Bản đồ dự án';
    $tenTinh = $tinhDangXem ? nx_ten_dia_gioi_ngan($tinhDangXem->province_name) : null;
@endphp

@include('frontend.noxh.component.page-head', [
    'crumbs' => $tenTinh
        ? ['Dự án' => url('/du-an'), $tenBanDo => url('/du-an/ban-do'), $tenTinh => '']
        : ['Dự án' => url('/du-an'), $tenBanDo => ''],
    'tieuDe' => $tenTinh ? 'Dự án nhà ở xã hội tại ' . $tenTinh : $tenBanDo,
    'moTa' => $intro['projectaside_map_note'] ?? '',
    'soLieu' => $soLieuDau,
])

<div class="nx__container nx-trang-ban-do">
    <div class="nx-trang-ban-do__hinh">
        @include('frontend.noxh.component.vn-map', ['diem' => $ghimBanDo])
    </div>

    <div class="nx-trang-ban-do__ds">
        <h2>Dự án theo tỉnh / thành phố</h2>

        @if(count($danhSach))
            <div class="nx-ban-do__tinh nx-ban-do__tinh--rong">
                @foreach($danhSach as $t)
                    <a href="{{ url('/du-an/ban-do?province_code=' . $t->province_code) }}"
                       class="{{ $maTinh === $t->province_code ? 'is-chon' : '' }}">
                        {{ nx_ten_dia_gioi_ngan($t->province_name) }}
                        <span>({{ $t->so_du_an }})</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="nx-empty">Chưa có dự án nào được đăng.</p>
        @endif

        <a href="{{ url('/du-an') }}" class="nx-btn nx-btn--ghost nx-btn--sm">
            @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 15])
            Về danh sách dự án
        </a>
    </div>
</div>

@if($duAn && $duAn->total())
    <div class="nx__container nx-trang-ban-do__duan">
        <h2 class="nx-trang-ban-do__tieude">
            {{ number_format($duAn->total(), 0, ',', '.') }} dự án tại {{ $tenTinh }}
        </h2>

        @foreach($duAn as $d)
            @include('frontend.noxh.component.project-row', ['d' => $d])
        @endforeach

        {{-- Giu ?province_code khi sang trang, neu khong trang 2 se ra toan quoc. --}}
        @include('frontend.noxh.component.pagination', ['model' => $duAn->appends(request()->query())])
    </div>
@endif
@endsection
