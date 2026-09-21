@extends('frontend.noxh.layout')

@section('content')
@php
    // Giu lai cac o loc dang bat khi doi mot o khac, neu khong moi lan tich
    // them mot muc la mat het nhung muc da chon truoc do.
    $dangChon = function ($ten, $gia) {
        return in_array($gia, (array) request($ten, []), true);
    };
@endphp

@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Dự án' => ''],
    'tieuDe' => $intro['project_heading'] ?? 'Danh sách dự án nhà ở xã hội',
    'moTa' => $intro['project_description'] ?? '',
    'soLieu' => [
        ['icon' => 'building', 'value' => number_format($tongDuAn, 0, ',', '.'), 'label' => 'Dự án toàn quốc'],
        ['icon' => 'pin', 'value' => $tongTinh, 'label' => 'Tỉnh / Thành phố'],
    ],
])

<div class="nx__container">
    <form method="GET" action="{{ url('/du-an') }}" class="nx-searchbar">
        <input type="text" name="tu-khoa" value="{{ request('tu-khoa') }}"
               placeholder="Tìm kiếm dự án (ví dụ: Túc Duyên, Hà Nội, Thái Nguyên...)">
        <button type="submit" class="nx-btn">Tìm kiếm</button>
    </form>
</div>

<div class="nx-listing">

    {{-- CỘT TRÁI: BỘ LỌC ---------------------------------------------------- --}}
    <aside>
        <form method="GET" action="{{ url('/du-an') }}" class="nx-panel">
            <h2 class="nx-panel__title">
                Lọc dự án
                @if(request()->hasAny(['province_code', 'status', 'gia', 'dien-tich', 'tu-khoa']))
                    <a href="{{ url('/du-an') }}">Xóa lọc</a>
                @endif
            </h2>

            @if(request('tu-khoa'))
                <input type="hidden" name="tu-khoa" value="{{ request('tu-khoa') }}">
            @endif

            <div class="nx-filter__group">
                <div class="nx-filter__legend">Tỉnh / Thành phố</div>
                <select name="province_code" class="nx-field" style="width:100%;padding:9px 11px;border:1px solid #e3ebf6;border-radius:8px">
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
                ÁP DỤNG BỘ LỌC
            </button>
        </form>

        @include('frontend.noxh.component.expert-box')
    </aside>

    {{-- CỘT GIỮA: DANH SÁCH -------------------------------------------------- --}}
    <div>
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
                <label for="sapxep">Sắp xếp:</label>
                <select id="sapxep" name="sap-xep" data-nx-auto-submit>
                    <option value="moi-nhat" {{ request('sap-xep') === 'moi-nhat' ? 'selected' : '' }}>Mới nhất</option>
                    <option value="gia-tang" {{ request('sap-xep') === 'gia-tang' ? 'selected' : '' }}>Giá thấp đến cao</option>
                    <option value="gia-giam" {{ request('sap-xep') === 'gia-giam' ? 'selected' : '' }}>Giá cao đến thấp</option>
                    <option value="dien-tich" {{ request('sap-xep') === 'dien-tich' ? 'selected' : '' }}>Diện tích nhỏ đến lớn</option>
                </select>
            </form>
        </div>

        @forelse($duAn as $d)
            @php
                $url = url('/du-an/' . $d->canonical);
                $tt = \App\Models\Product::TRANG_THAI_DU_AN[$d->status] ?? null;
            @endphp
            <article class="nx-project-row">
                <a href="{{ $url }}" class="nx-project-row__media" title="{{ $d->name }}">
                    @if(!empty($d->image))
                        <img src="{{ $d->image }}" alt="{{ $d->name }}" loading="lazy">
                    @endif
                    @if($tt)
                        <span class="nx-badge nx-badge--{{ $d->status }} nx-project-row__badge">{{ $tt }}</span>
                    @endif
                </a>

                <div>
                    <div class="nx-project-row__head">
                        <div>
                            <h2 class="nx-project-row__title"><a href="{{ $url }}">{{ $d->name }}</a></h2>
                            @if($d->province_name)
                                <div class="nx-project-row__place">
                                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 14])
                                    {{ $d->province_name }}
                                </div>
                            @endif
                        </div>
                        <div class="nx-project-row__price">
                            {{ khoang_gia($d->price_from, $d->price_to) }}
                            <small>triệu/m²</small>
                        </div>
                    </div>

                    @if($d->description)
                        <p class="nx-project-row__description">{{ \Illuminate\Support\Str::words(strip_tags($d->description), 28, '…') }}</p>
                    @endif

                    <div class="nx-project-row__meta">
                        <div>
                            @include('frontend.noxh.component.icon', ['name' => 'ruler', 'size' => 15])
                            Diện tích <strong>{{ khoang_so($d->area_from, $d->area_to, ' m²') }}</strong>
                        </div>
                        @if($d->total_units)
                            <div>
                                @include('frontend.noxh.component.icon', ['name' => 'layers', 'size' => 15])
                                Số căn <strong>{{ number_format($d->total_units, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                        @if($tt)
                            <div>
                                @include('frontend.noxh.component.icon', ['name' => 'building', 'size' => 15])
                                Tiến độ <strong>{{ $tt }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="nx-project-row__foot">
                        <span class="nx-project-row__updated">
                            Cập nhật: {{ \Illuminate\Support\Carbon::parse($d->updated_at)->format('d/m/Y') }}
                        </span>
                        <span style="display:flex;gap:8px">
                            <a href="{{ $url }}" class="nx-btn nx-btn--ghost nx-btn--sm">Xem chi tiết</a>
                            <a href="{{ $url }}#dang-ky" class="nx-btn nx-btn--sm">Đăng ký tư vấn</a>
                        </span>
                    </div>
                </div>
            </article>
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
        <div class="nx-panel">
            <h2 class="nx-panel__title">
                Dự án theo tỉnh
                <a href="{{ url('/du-an/tinh-thanh') }}">Xem tất cả</a>
            </h2>
            <div class="nx-chips">
                @foreach($tinhCoDuAn as $t)
                    <a href="{{ url('/du-an?province_code=' . $t->province_code) }}"
                       class="{{ request('province_code') == $t->province_code ? 'is-active' : '' }}">
                        {{ $t->province_name }} ({{ $t->so_du_an }})
                    </a>
                @endforeach
            </div>
        </div>

        <div class="nx-panel">
            <h2 class="nx-panel__title">Có dự án phù hợp với bạn?</h2>
            <ul class="nx-ticks nx-ticks--green">
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Tư vấn chọn dự án theo nhu cầu</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Kiểm tra điều kiện mua</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Hỗ trợ chuẩn bị hồ sơ</li>
            </ul>
            <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--amber nx-btn--block">
                KIỂM TRA ĐIỀU KIỆN NGAY
            </a>
        </div>
    </aside>
</div>
@endsection
