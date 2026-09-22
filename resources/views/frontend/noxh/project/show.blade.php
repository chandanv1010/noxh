@extends('frontend.noxh.layout')

@section('content')
@php
    $tt = \App\Models\Product::TRANG_THAI_DU_AN[$duAn->status] ?? null;

    $thongSo = array_filter([
        ['icon' => 'users', 'nhan' => 'Chủ đầu tư', 'giaTri' => $duAn->investor_name],
        ['icon' => 'home', 'nhan' => 'Loại hình', 'giaTri' => 'Nhà ở xã hội'],
        ['icon' => 'ruler', 'nhan' => 'Tổng diện tích', 'giaTri' => $duAn->total_land_area ? so_gon($duAn->total_land_area) . ' ha' : null],
        ['icon' => 'layers', 'nhan' => 'Quy mô', 'giaTri' => $duAn->total_units ? number_format($duAn->total_units, 0, ',', '.') . ' căn' : null],
        ['icon' => 'calendar', 'nhan' => 'Thời gian triển khai', 'giaTri' => $duAn->start_date ? \Illuminate\Support\Carbon::parse($duAn->start_date)->format('Y') . ($duAn->handover_date ? ' – ' . \Illuminate\Support\Carbon::parse($duAn->handover_date)->format('Y') : '') : null],
        ['icon' => 'check-circle', 'nhan' => 'Bàn giao dự kiến', 'giaTri' => $duAn->timeline_label],
    ], fn($o) => !empty($o['giaTri']));
@endphp

@include('frontend.noxh.component.crumb', [
    'crumbs' => ['Dự án' => url('/du-an'), $duAn->name => ''],
])

<div class="nx__container">
    <section class="nx-detail-hero">
        @if(!empty($duAn->image))
            <div class="nx-detail-hero__media">
                <img src="{{ $duAn->image }}" alt="{{ $duAn->name }}" fetchpriority="high" decoding="async">
            </div>
        @endif

        <div class="nx-detail-hero__inner">
            @if($tt)
                <span class="nx-badge nx-badge--{{ $duAn->status }}">{{ $tt }}</span>
            @endif

            <h1 class="nx-detail-hero__title">{{ $duAn->name }}</h1>

            @if($duAn->address || $duAn->province_name)
                <div class="nx-detail-hero__place">
                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 16])
                    {{ trim(($duAn->address ? $duAn->address . ', ' : '') . $duAn->province_name, ', ') }}
                </div>
            @endif

            @if($duAn->description)
                <p class="nx-detail-hero__description">{{ strip_tags($duAn->description) }}</p>
            @endif
        </div>
    </section>

    @if(count($thongSo))
        <dl class="nx-spec-strip">
            @foreach($thongSo as $o)
                <div>
                    <dt>
                        @include('frontend.noxh.component.icon', ['name' => $o['icon'], 'size' => 14])
                        {{ $o['nhan'] }}
                    </dt>
                    <dd>{{ $o['giaTri'] }}</dd>
                </div>
            @endforeach
        </dl>
    @endif
</div>

<div class="nx-detail">
    <div>
        {{-- TỔNG QUAN ------------------------------------------------------- --}}
        <div class="nx-panel">
            <h2 class="nx-panel__title">Tổng quan dự án</h2>

            <table class="nx-table">
                <tbody>
                    <tr><th>Tên dự án</th><td>{{ $duAn->name }}</td></tr>
                    @if($duAn->address || $duAn->province_name)
                        <tr><th>Vị trí</th><td>{{ trim(($duAn->address ? $duAn->address . ', ' : '') . $duAn->province_name, ', ') }}</td></tr>
                    @endif
                    @if($duAn->investor_name)
                        <tr><th>Chủ đầu tư</th><td>{{ $duAn->investor_name }}</td></tr>
                    @endif
                    @if($duAn->total_land_area)
                        <tr><th>Tổng diện tích</th><td>{{ so_gon($duAn->total_land_area) }} ha</td></tr>
                    @endif
                    @if($duAn->scale_description)
                        <tr><th>Quy mô</th><td>{{ $duAn->scale_description }}</td></tr>
                    @endif
                    @if($duAn->total_units)
                        <tr><th>Tổng số căn</th><td>{{ number_format($duAn->total_units, 0, ',', '.') }} căn</td></tr>
                    @endif
                    @if($duAn->apartment_types)
                        <tr><th>Loại hình căn hộ</th><td>{{ $duAn->apartment_types }}</td></tr>
                    @endif
                    <tr><th>Diện tích căn hộ</th><td>{{ khoang_so($duAn->area_from, $duAn->area_to, ' m²') }}</td></tr>
                    <tr><th>Giá bán dự kiến</th><td>{{ khoang_gia($duAn->price_from, $duAn->price_to) }} triệu/m²</td></tr>
                    @if($duAn->ownership_type)
                        <tr><th>Hình thức sở hữu</th><td>{{ $duAn->ownership_type }}</td></tr>
                    @endif
                    @if($duAn->timeline_label)
                        <tr><th>Dự kiến bàn giao</th><td>{{ $duAn->timeline_label }}</td></tr>
                    @endif
                    @if($tt)
                        <tr><th>Trạng thái</th><td><span class="nx-badge nx-badge--{{ $duAn->status }}">{{ $tt }}</span></td></tr>
                    @endif
                </tbody>
            </table>
        </div>

        {{-- HÌNH ẢNH -------------------------------------------------------- --}}
        @if(count($album))
            <div class="nx-panel">
                <h2 class="nx-panel__title">Hình ảnh dự án</h2>
                <div class="nx-unit-grid">
                    @foreach($album as $anh)
                        <div class="nx-unit__media" style="border-radius:10px;overflow:hidden;aspect-ratio:4/3">
                            <img src="{{ $anh }}" alt="{{ $duAn->name }}" loading="lazy"
                                 style="width:100%;height:100%;object-fit:cover">
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- NỘI DUNG -------------------------------------------------------- --}}
        @if($duAn->content)
            <div class="nx-panel">
                <h2 class="nx-panel__title">Giới thiệu chi tiết</h2>
                <div class="nx-prose">{!! $duAn->content !!}</div>
            </div>
        @endif

        {{-- TIẾN ĐỘ --------------------------------------------------------- --}}
        @if($tienDo->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Tiến độ dự án</h2>
                <ul class="nx-timeline">
                    @foreach($tienDo as $moc)
                        <li class="{{ $moc->status === 'done' ? 'is-done' : ($moc->status === 'doing' ? 'is-doing' : '') }}">
                            <strong>{{ $moc->title }}</strong>
                            <span>{{ $moc->date_label ?: ($moc->sort_date ? \Illuminate\Support\Carbon::parse($moc->sort_date)->format('m/Y') : '') }}</span>
                            @if($moc->description)
                                <span>{{ $moc->description }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PHÁP LÝ --------------------------------------------------------- --}}
        @if($hoSo->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Pháp lý dự án</h2>
                <div class="nx-doc-grid">
                    @foreach($hoSo as $hs)
                        <a href="{{ $hs->file ?: '#' }}" class="nx-doc" @if($hs->file) target="_blank" rel="noopener" @endif>
                            <span class="nx-doc__icon">
                                @include('frontend.noxh.component.icon', ['name' => 'file-text', 'size' => 22])
                            </span>
                            <span>
                                <strong>{{ $hs->title }}</strong>
                                @if($hs->doc_number)<span>Số: {{ $hs->doc_number }}</span>@endif
                                @if($hs->issued_date)<span>Ngày: {{ \Illuminate\Support\Carbon::parse($hs->issued_date)->format('d/m/Y') }}</span>@endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- CÂU HỎI THƯỜNG GẶP ---------------------------------------------- --}}
        @if($faq->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Câu hỏi thường gặp</h2>
                @foreach($faq as $ch)
                    <details class="nx-faq">
                        <summary>{{ $ch->question }}</summary>
                        <div class="nx-faq__body">{!! nl2br(e(strip_tags($ch->answer))) !!}</div>
                    </details>
                @endforeach
            </div>
        @endif

        {{-- DỰ ÁN TƯƠNG TỰ -------------------------------------------------- --}}
        @if($tuongTu->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Dự án cùng khu vực</h2>
                <div class="nx-project-grid nx-project-grid--3">
                    @foreach($tuongTu as $d)
                        @include('frontend.noxh.component.project-card', ['duAn' => $d])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- CỘT PHẢI --------------------------------------------------------- --}}
    <aside id="dang-ky">
        @include('frontend.noxh.component.lead-form', [
            'tieuDe' => 'Bạn quan tâm dự án?',
            'moTa' => 'Để lại thông tin, chúng tôi sẽ liên hệ hỗ trợ bạn sớm nhất.',
            'nguon' => 'project',
            'duAnId' => $duAn->id,
        ])

        @if($duAn->investor_name)
            <div class="nx-panel">
                <h2 class="nx-panel__title">Thông tin liên hệ dự án</h2>
                <table class="nx-table">
                    <tbody>
                        <tr><th>Chủ đầu tư</th><td>{{ $duAn->investor_name }}</td></tr>
                        @if($duAn->investor_hotline)
                            <tr><th>Hotline</th><td><a href="tel:{{ preg_replace('/[^0-9+]/', '', $duAn->investor_hotline) }}">{{ $duAn->investor_hotline }}</a></td></tr>
                        @endif
                        @if($duAn->investor_email)
                            <tr><th>Email</th><td>{{ $duAn->investor_email }}</td></tr>
                        @endif
                        @if($duAn->investor_website)
                            <tr><th>Website</th><td>{{ $duAn->investor_website }}</td></tr>
                        @endif
                        @if($duAn->investor_address)
                            <tr><th>Địa chỉ</th><td>{{ $duAn->investor_address }}</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        @endif

        @include('frontend.noxh.component.sale-staff')

        @include('frontend.noxh.component.expert-box')

        <div class="nx-panel">
            <h2 class="nx-panel__title">Bạn đã đủ điều kiện mua?</h2>
            <p class="nx__subheading">Trả lời 8 câu hỏi ngắn để biết khả năng đáp ứng điều kiện mua NOXH.</p>
            <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--block">KIỂM TRA ĐIỀU KIỆN</a>
        </div>
    </aside>
</div>
@endsection
