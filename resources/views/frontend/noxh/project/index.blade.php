@extends('frontend.noxh.layout')

@section('content')
@php
    // Giu lai cac o loc dang bat khi doi mot o khac, neu khong moi lan tich
    // them mot muc la mat het nhung muc da chon truoc do.
    $dangChon = function ($ten, $gia) {
        return in_array($gia, (array) request($ten, []), true);
    };

    $dangLoc = request()->hasAny(['province_code', 'status', 'gia', 'dien-tich', 'tu-khoa']);
@endphp

@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Dự án' => ''],
    'tieuDe' => $intro['project_heading'] ?? 'Danh sách dự án Nhà ở xã hội',
    'moTa' => $intro['project_description'] ?? '',
    'soLieu' => $soLieuDau,
])

{{-- THANH TIM KIEM ---------------------------------------------------------
     Chi trum hai cot trai + giua, khong keo sang cot ban do ben phai:
     lop nx-tim-du-an nam trong cung luoi ba cot cua .nx-listing va chiem hai
     o dau (xem _noxh-page.scss). --}}
<div class="nx-listing nx-listing--du-an">

    <form method="GET" action="{{ url('/du-an') }}" class="nx-tim-du-an">
        <label class="nx-tim-du-an__o">
            <span class="nx-an-di">Từ khoá tìm dự án</span>
            @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 18])
            <input type="text" name="tu-khoa" value="{{ request('tu-khoa') }}"
                   placeholder="{{ $intro['project_search_placeholder'] ?? 'Tìm kiếm dự án (ví dụ: Túc Duyên, Hà Nội, Thái Nguyên...)' }}">
        </label>
        <button type="submit" class="nx-btn">{{ $intro['project_search_button'] ?? 'Tìm kiếm' }}</button>
    </form>

    {{-- CỘT TRÁI: BỘ LỌC ---------------------------------------------------- --}}
    <aside class="nx-listing__trai">
        <form method="GET" action="{{ url('/du-an') }}" class="nx-panel nx-bo-loc">
            <h2 class="nx-panel__title nx-bo-loc__dau">
                {{ $intro['project_filter_heading'] ?? 'Lọc dự án' }}
                {{-- Luon hien: nguoi dung phai thay duong thoat truoc khi
                     tich chon, chu khong phai sau khi da loc roi moi biet. --}}
                <a href="{{ url('/du-an') }}" class="{{ $dangLoc ? '' : 'is-mo-nhat' }}">
                    {{ $intro['project_filter_clear'] ?? 'Xóa lọc' }}
                </a>
            </h2>

            @if(request('tu-khoa'))
                <input type="hidden" name="tu-khoa" value="{{ request('tu-khoa') }}">
            @endif

            <div class="nx-filter__group">
                <div class="nx-filter__legend">Tỉnh / Thành phố</div>
                <select name="province_code" data-nx-chon>
                    <option value="">Tất cả tỉnh / thành</option>
                    @foreach($tinhThanh as $t)
                        <option value="{{ $t->code }}" {{ request('province_code') == $t->code ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="nx-filter__group">
                <div class="nx-filter__legend">Trạng thái</div>
                @foreach(\App\Models\Product::TRANG_THAI_DU_AN as $ma => $ten)
                    <label class="nx-filter__option">
                        <input type="checkbox" name="status[]" value="{{ $ma }}" {{ $dangChon('status', $ma) ? 'checked' : '' }}>
                        <span>{{ $ten }}</span>
                        <span>{{ $demTrangThai[$ma] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>

            <div class="nx-filter__group">
                <div class="nx-filter__legend">Mức giá (triệu/m²)</div>
                @foreach($khoangGia as $ma => $m)
                    <label class="nx-filter__option">
                        <input type="checkbox" name="gia[]" value="{{ $ma }}" {{ $dangChon('gia', $ma) ? 'checked' : '' }}>
                        <span>{{ $m['nhan'] }}</span>
                        <span>{{ $demGia[$ma] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>

            <div class="nx-filter__group">
                <div class="nx-filter__legend">Diện tích căn hộ (m²)</div>
                @foreach($khoangDienTich as $ma => $m)
                    <label class="nx-filter__option">
                        <input type="checkbox" name="dien-tich[]" value="{{ $ma }}" {{ $dangChon('dien-tich', $ma) ? 'checked' : '' }}>
                        <span>{{ $m['nhan'] }}</span>
                        <span>{{ $demDienTich[$ma] ?? 0 }}</span>
                    </label>
                @endforeach
            </div>

            <button type="submit" class="nx-btn nx-btn--block">
                @include('frontend.noxh.component.icon', ['name' => 'filter', 'size' => 17])
                {{ $intro['project_filter_button'] ?? 'ÁP DỤNG BỘ LỌC' }}
            </button>
        </form>

        @include('frontend.noxh.component.advisor-banner')
    </aside>

    {{-- CỘT GIỮA: DANH SÁCH -------------------------------------------------- --}}
    <div class="nx-listing__giua">
        <div class="nx-toolbar">
            <div class="nx-toolbar__count">
                Hiển thị <strong>{{ $duAn->firstItem() ?: 0 }} – {{ $duAn->lastItem() ?: 0 }}</strong>
                trong <strong>{{ number_format($duAn->total(), 0, ',', '.') }}</strong> dự án
            </div>

            <form method="GET" class="nx-toolbar__sort">
                @foreach(request()->except(['sap-xep', 'page']) as $k => $v)
                    @foreach((array) $v as $vv)
                        <input type="hidden" name="{{ $k }}{{ is_array($v) ? '[]' : '' }}" value="{{ $vv }}">
                    @endforeach
                @endforeach
                <label for="sapxep">{{ $intro['project_sort_label'] ?? 'Sắp xếp:' }}</label>
                <select id="sapxep" name="sap-xep" data-nx-chon data-nx-auto-submit>
                    <option value="moi-nhat" {{ request('sap-xep') === 'moi-nhat' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="gia-tang" {{ request('sap-xep') === 'gia-tang' ? 'selected' : '' }}>Giá thấp đến cao</option>
                    <option value="gia-giam" {{ request('sap-xep') === 'gia-giam' ? 'selected' : '' }}>Giá cao đến thấp</option>
                    <option value="dien-tich" {{ request('sap-xep') === 'dien-tich' ? 'selected' : '' }}>Diện tích nhỏ đến lớn</option>
                </select>
            </form>
        </div>

        @forelse($duAn as $d)
            @include('frontend.noxh.component.project-row', ['d' => $d])
        @empty
            <div class="nx-empty">
                Không tìm thấy dự án nào khớp với bộ lọc.
                <br><a href="{{ url('/du-an') }}">Xóa bộ lọc và xem tất cả →</a>
            </div>
        @endforelse

        @include('frontend.noxh.component.pagination', ['model' => $duAn])
    </div>

    {{-- CỘT PHẢI ------------------------------------------------------------ --}}
    <aside class="nx-listing__aside-right">
        @include('frontend.noxh.component.map-box')
        @include('frontend.noxh.component.news-box')
        @include('frontend.noxh.component.fit-box')
    </aside>
</div>
@endsection
