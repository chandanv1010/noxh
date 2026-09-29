@extends('frontend.noxh.layout')

@section('content')
@php
    use App\Classes\Introduce;

    $tt = \App\Models\Product::TRANG_THAI_DU_AN[$duAn->status] ?? null;
    $thay = ['{tinh}' => $tenTinh ?: 'khu vực'];

    $anhNen = trim((string) ($intro['projectdetail_hero_bg'] ?? ''));

    // Khau hieu viet tay o goc phai: moi dong mot cau.
    $khauHieu = array_values(array_filter(array_map(
        'trim',
        preg_split('/\r\n|\r|\n/', (string) ($intro['projectdetail_hero_slogan'] ?? '')) ?: []
    ), fn ($d) => $d !== ''));

    $noiDuAn = trim(($duAn->address ? $duAn->address . ', ' : '')
        . implode(', ', array_filter([$duAn->ward_name, $duAn->province_name])), ', ');

    // Duong dan dieu huong co them mot cap tinh/thanh so voi cac trang khac -
    // ban thiet ke ve "Trang chu > Du an > Thai Nguyen > NOXH Tuc Duyen".
    $duongDan = ['Dự án' => url('/du-an')];

    if ($tenTinh !== '' && $duAn->province_code) {
        $duongDan[$tenTinh] = url('/du-an/ban-do?province_code=' . $duAn->province_code);
    }

    $duongDan[$duAn->name] = '';

    $giaDuAn = khoang_so($duAn->price_from, $duAn->price_to, ' triệu/m²', '');
    $coBanDo = !empty($duAn->map_image) || $banDoUrl;
    $chuTrong = $intro['projectdetail_empty_text'] ?? 'Đang cập nhật';

    // Mot tab CHI hien khi co du lieu that: du an chua nhap tien do thi khong
    // co tab dan sang mot khung trong.
    $coDuLieu = [
        'overview' => count($bangTongQuan) > 0,
        'location' => (bool) $coBanDo,
        'units' => $tatCaLoaiCanHo->count() > 0 || !empty($duAn->site_plan_image),
        'amenity' => $tienIch->count() > 0,
        'price' => $giaDuAn !== '' || $tatCaLoaiCanHo->count() > 0,
        'progress' => $tatCaTienDo->count() > 0,
        'legal' => $hoSo->count() > 0,
        'gallery' => count($album) > 0 || !empty($duAn->video_url),
        'doc' => $taiLieu->count() > 0,
        'faq' => $faq->count() > 0,
    ];

    // Hinh mac dinh cua tung tab - quan tri doi duoc trong Cau hinh chung.
    $iconMac = [
        'overview' => 'tab-overview', 'location' => 'tab-location', 'units' => 'tab-plan',
        'amenity' => 'tab-amenity', 'price' => 'tab-price', 'progress' => 'tab-progress',
        'legal' => 'tab-legal', 'gallery' => 'tab-gallery', 'doc' => 'tab-doc',
        'faq' => 'tab-faq',
    ];

    $tab = [];
    $tieuDe = [];

    foreach (Introduce::TAB_CHI_TIET as $ma => $ten) {
        $tieuDe[$ma] = strtr((string) ($intro['projectdetail_' . $ma . '_heading'] ?? ''), $thay)
            ?: mb_strtoupper($ten . ' dự án');

        if ($coDuLieu[$ma]) {
            $tab[] = [
                'ma' => $ma,
                'ten' => $intro['projectdetail_' . $ma . '_tab'] ?? $ten,
                'icon' => $intro['projectdetail_' . $ma . '_icon'] ?? $iconMac[$ma],
            ];
        }
    }

    $tabDau = count($tab) ? $tab[0]['ma'] : null;
@endphp

<div class="nx-pd">

    {{-- ĐẦU TRANG: tiêu đề + ảnh + thẻ giá ------------------------------ --}}
    <div class="nx-pd__dau{{ $anhNen !== '' ? ' co-nen' : '' }}"
         @if($anhNen !== '') style="--nx-nen: url('{{ e($anhNen) }}')" @endif>
        <div class="nx__container">
            @include('frontend.noxh.component.crumb', ['crumbs' => $duongDan])

            <div class="nx-pd__head">
                <div>
                    <h1 class="nx-pd__ten">
                        {{ $duAn->name }}
                        @if($tt)
                            <span class="nx-badge nx-badge--{{ $duAn->status }}">{{ $tt }}</span>
                        @endif
                    </h1>

                    @if($noiDuAn !== '')
                        <p class="nx-pd__noi">
                            @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                            {{ $noiDuAn }}
                        </p>
                    @endif
                </div>

                @if(count($khauHieu))
                    <p class="nx-pd__khau" aria-hidden="true">
                        @foreach($khauHieu as $d)
                            <span>{{ $d }}</span>
                        @endforeach
                    </p>
                @endif
            </div>

            <div class="nx-pd__tren">
                @include('frontend.noxh.component.pd-gallery')
                @include('frontend.noxh.component.pd-price')
            </div>
        </div>
    </div>

    @include('frontend.noxh.component.pd-tabs', ['tab' => $tab, 'tabDau' => $tabDau])

    <div class="nx__container nx-pd__than">

        {{-- CỘT TRÁI ---------------------------------------------------- --}}
        <div class="nx-pd__chinh">

            {{-- KHUNG ĐỔI NỘI DUNG THEO TAB.
                 Không có JS thì tất cả các khối cùng hiện ra nên vẫn đọc được
                 hết; JS gắn class is-tab rồi mới giấu bớt. --}}
            @if(count($tab))
                <div class="nx-panel nx-pd-khung" id="khung-tab">

                    {{-- TAB: TỔNG QUAN --}}
                    @if($coDuLieu['overview'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'overview' ? ' is-hien' : '' }}"
                                 id="tab-overview" data-nx-pane="overview">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['overview'] }}</h2>

                            <div class="nx-pd-tq__trong">
                                <table class="nx-pd-bang">
                                    <tbody>
                                        @foreach($bangTongQuan as $nhan => $giaTri)
                                            <tr><th>{{ $nhan }}</th><td>{{ $giaTri }}</td></tr>
                                        @endforeach
                                        @if($tt && $nhanTrangThai !== '')
                                            <tr>
                                                <th>{{ $nhanTrangThai }}</th>
                                                <td><span class="nx-badge nx-badge--{{ $duAn->status }}">{{ $tt }}</span></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>

                                @if(!empty($duAn->site_plan_image))
                                    <figure class="nx-pd-tq__so-do">
                                        <img src="{{ $duAn->site_plan_image }}"
                                             alt="Mặt bằng tổng thể {{ $duAn->name }}" loading="lazy" decoding="async">
                                        @if(count($album))
                                            <button type="button" class="nx-pd-tq__nut" data-nx-mo-tab="gallery">
                                                @include('frontend.noxh.component.icon', ['name' => 'photo', 'size' => 18])
                                                {{ $intro['projectdetail_overview_photo_text'] ?? 'Xem ảnh thực tế' }}
                                            </button>
                                        @endif
                                    </figure>
                                @endif
                            </div>

                            @if($duAn->content)
                                <div class="nx-pd-gioi">
                                    <h3>{{ $intro['projectdetail_content_heading'] ?? 'GIỚI THIỆU CHI TIẾT' }}</h3>
                                    <div class="nx-prose">{!! $duAn->content !!}</div>
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- TAB: VỊ TRÍ --}}
                    @if($coDuLieu['location'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'location' ? ' is-hien' : '' }}"
                                 id="tab-location" data-nx-pane="location">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['location'] }}</h2>

                            <div class="nx-pd-vitri__khung nx-pd-vitri__khung--to">
                                @if(!empty($duAn->map_image))
                                    <img src="{{ $duAn->map_image }}"
                                         alt="Bản đồ vị trí {{ $duAn->name }}" loading="lazy" decoding="async">
                                    <span class="nx-pd-vitri__ghim">
                                        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                                        {{ $duAn->name }}
                                    </span>
                                @endif

                                @if($banDoUrl)
                                    <a href="{{ $banDoUrl }}" class="nx-pd-vitri__nut"
                                       target="_blank" rel="noopener nofollow">
                                        @include('frontend.noxh.component.icon', ['name' => 'directions', 'size' => 18])
                                        {{ $intro['projectdetail_location_button'] ?? 'Xem trên Google Maps' }}
                                    </a>
                                @endif
                            </div>

                            @if($noiDuAn !== '')
                                <p class="nx-pd-khung__dong">
                                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                                    {{ $noiDuAn }}
                                </p>
                            @endif
                        </section>
                    @endif

                    {{-- TAB: MẶT BẰNG --}}
                    @if($coDuLieu['units'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'units' ? ' is-hien' : '' }}"
                                 id="tab-units" data-nx-pane="units">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['units'] }}</h2>

                            @if(!empty($duAn->site_plan_image))
                                <figure class="nx-pd-mb__tong">
                                    <img src="{{ $duAn->site_plan_image }}"
                                         alt="Mặt bằng tổng thể {{ $duAn->name }}" loading="lazy" decoding="async">
                                </figure>
                            @endif

                            @if($tatCaLoaiCanHo->count())
                                <div class="nx-pd-mb__luoi">
                                    @foreach($tatCaLoaiCanHo as $can)
                                        @if($can->image)
                                            <figure class="nx-pd-mb__o">
                                                <a href="{{ $can->image }}" target="_blank" rel="noopener">
                                                    <img src="{{ $can->image }}" alt="Mặt bằng {{ $can->name }}"
                                                         loading="lazy" decoding="async">
                                                </a>
                                                <figcaption>
                                                    <strong>{{ $can->name }}</strong>
                                                    @php $dt = khoang_so($can->area_from, $can->area_to, ' m²', ''); @endphp
                                                    @if($dt !== '')<span>{{ $dt }}</span>@endif
                                                </figcaption>
                                            </figure>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- TAB: TIỆN ÍCH --}}
                    @if($coDuLieu['amenity'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'amenity' ? ' is-hien' : '' }}"
                                 id="tab-amenity" data-nx-pane="amenity">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['amenity'] }}</h2>

                            <div class="nx-pd-tienich">
                                @foreach($tienIch as $ti)
                                    <div class="nx-pd-o">
                                        @include('frontend.noxh.component.icon', [
                                            'name' => $ti->icon ?: 'check-circle', 'size' => 24,
                                        ])
                                        <span>
                                            <span class="nx-pd-o__nhan">{{ $ti->title }}</span>
                                            @if($ti->subtitle)<strong>{{ $ti->subtitle }}</strong>@endif
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- TAB: GIÁ BÁN --}}
                    @if($coDuLieu['price'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'price' ? ' is-hien' : '' }}"
                                 id="tab-price" data-nx-pane="price">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['price'] }}</h2>

                            @if($giaDuAn !== '')
                                <p class="nx-pd-gb__so">
                                    {{ $giaDuAn }}
                                    @if(!empty($intro['projectdetail_price_note']))
                                        <span>{{ $intro['projectdetail_price_note'] }}</span>
                                    @endif
                                </p>
                            @endif

                            @if($tatCaLoaiCanHo->count())
                                <table class="nx-pd-bang nx-pd-bang--gia">
                                    <thead>
                                        <tr>
                                            <th>{{ $intro['projectdetail_price_col_name'] ?? 'Loại căn hộ' }}</th>
                                            <th>{{ $intro['projectdetail_price_col_area'] ?? 'Diện tích' }}</th>
                                            <th>{{ $intro['projectdetail_price_col_price'] ?? 'Giá dự kiến' }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tatCaLoaiCanHo as $can)
                                            <tr>
                                                <th>{{ $can->name }}</th>
                                                <td>{{ khoang_so($can->area_from, $can->area_to, ' m²', $chuTrong) }}</td>
                                                <td><strong>{{ khoang_so($can->price_from, $can->price_to, ' ' . $can->price_unit, $chuTrong, 3) }}</strong></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </section>
                    @endif

                    {{-- TAB: TIẾN ĐỘ --}}
                    @if($coDuLieu['progress'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'progress' ? ' is-hien' : '' }}"
                                 id="tab-progress" data-nx-pane="progress">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['progress'] }}</h2>

                            @include('frontend.noxh.component.pd-progress', [
                                'moc' => $tatCaTienDo, 'coAnh' => true,
                            ])
                        </section>
                    @endif

                    {{-- TAB: PHÁP LÝ --}}
                    @if($coDuLieu['legal'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'legal' ? ' is-hien' : '' }}"
                                 id="tab-legal" data-nx-pane="legal">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['legal'] }}</h2>
                            @include('frontend.noxh.component.pd-docs', ['giayTo' => $hoSo])
                        </section>
                    @endif

                    {{-- TAB: HÌNH ẢNH - VIDEO --}}
                    @if($coDuLieu['gallery'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'gallery' ? ' is-hien' : '' }}"
                                 id="tab-gallery" data-nx-pane="gallery">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['gallery'] }}</h2>

                            @if(!empty($duAn->video_url))
                                <button type="button" class="nx-pd-album__video"
                                        data-nx-video="{{ e($duAn->video_url) }}">
                                    @include('frontend.noxh.component.icon', ['name' => 'video', 'size' => 20])
                                    {{ $intro['projectdetail_gallery_video_text'] ?? 'Xem video dự án' }}
                                </button>
                            @endif

                            @if(count($album))
                                <div class="nx-pd-album">
                                    @foreach($album as $a)
                                        <a href="{{ $a }}" target="_blank" rel="noopener">
                                            <img src="{{ $a }}" alt="{{ $duAn->name }}" loading="lazy" decoding="async">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- TAB: TÀI LIỆU --}}
                    @if($coDuLieu['doc'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'doc' ? ' is-hien' : '' }}"
                                 id="tab-doc" data-nx-pane="doc">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['doc'] }}</h2>
                            @include('frontend.noxh.component.pd-docs', ['giayTo' => $taiLieu])
                        </section>
                    @endif

                    {{-- TAB: HỎI ĐÁP --}}
                    @if($coDuLieu['faq'])
                        <section class="nx-pd-khung__o{{ $tabDau === 'faq' ? ' is-hien' : '' }}"
                                 id="tab-faq" data-nx-pane="faq">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['faq'] }}</h2>
                            @foreach($faq as $ch)
                                <details class="nx-faq">
                                    <summary>{{ $ch->question }}</summary>
                                    <div class="nx-faq__body">{!! nl2br(e(strip_tags($ch->answer))) !!}</div>
                                </details>
                            @endforeach
                        </section>
                    @endif
                </div>
            @endif

            {{-- CÁC LOẠI CĂN HỘ --}}
            @if($loaiCanHo->count())
                <section class="nx-panel" id="loai-can-ho">
                    <h2 class="nx-pd__tieude nx-pd__tieude--co-nut">
                        {{ $intro['projectdetail_units_block_heading'] ?? 'CÁC LOẠI CĂN HỘ' }}
                        @if($conLoaiCanHo > 0)
                            <button type="button" data-nx-mo-tab="price">
                                {{ $intro['projectdetail_units_all_text'] ?? 'Xem tất cả' }}
                                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
                            </button>
                        @endif
                    </h2>

                    <div class="nx-pd-can-luoi">
                        @foreach($loaiCanHo as $can)
                            @include('frontend.noxh.component.pd-unit', ['can' => $can])
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- VỊ TRÍ + TIẾN ĐỘ: hai khối cạnh nhau như bản vẽ --}}
            @if($coBanDo || $tienDo->count())
                <div class="nx-pd-doi">
                    @if($coBanDo)
                        <section class="nx-panel nx-pd-vitri" id="vi-tri">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['location'] }}</h2>

                            <div class="nx-pd-vitri__khung">
                                @if(!empty($duAn->map_image))
                                    <img src="{{ $duAn->map_image }}"
                                         alt="Bản đồ vị trí {{ $duAn->name }}" loading="lazy" decoding="async">
                                    <span class="nx-pd-vitri__ghim">
                                        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 18])
                                        {{ $duAn->name }}
                                    </span>
                                @endif

                                @if($banDoUrl)
                                    <a href="{{ $banDoUrl }}" class="nx-pd-vitri__nut"
                                       target="_blank" rel="noopener nofollow">
                                        @include('frontend.noxh.component.icon', ['name' => 'directions', 'size' => 18])
                                        {{ $intro['projectdetail_location_button'] ?? 'Xem trên Google Maps' }}
                                    </a>
                                @endif
                            </div>
                        </section>
                    @endif

                    @if($tienDo->count())
                        <section class="nx-panel nx-pd-tiendo" id="tien-do">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['progress'] }}</h2>

                            <div class="nx-pd-tiendo__trong">
                                @include('frontend.noxh.component.pd-progress', [
                                    'moc' => $tienDo, 'coAnh' => false,
                                ])

                                @if(!empty($duAn->progress_image))
                                    <figure class="nx-pd-tiendo__anh">
                                        <img src="{{ $duAn->progress_image }}"
                                             alt="Hình ảnh thi công {{ $duAn->name }}" loading="lazy" decoding="async">
                                    </figure>
                                @endif
                            </div>

                            <button type="button" class="nx-pd-tiendo__nut" data-nx-mo="tien-do-day">
                                @include('frontend.noxh.component.icon', ['name' => 'update', 'size' => 18])
                                {{ $intro['projectdetail_progress_button'] ?? 'Xem cập nhật tiến độ' }}
                            </button>
                        </section>
                    @endif
                </div>
            @endif

            {{-- DỰ ÁN TƯƠNG TỰ --}}
            @if($tuongTu->count())
                <section class="nx-panel" id="tuong-tu">
                    <h2 class="nx-pd__tieude nx-pd__tieude--co-nut">
                        {{ strtr($intro['projectdetail_similar_heading'] ?? 'DỰ ÁN TƯƠNG TỰ', $thay) }}
                        <a href="{{ $duAn->province_code ? url('/du-an?province_code=' . $duAn->province_code) : url('/du-an') }}">
                            {{ $intro['projectdetail_similar_all_text'] ?? 'Xem tất cả' }}
                            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
                        </a>
                    </h2>

                    <div class="nx-pd-tt-luoi">
                        @foreach($tuongTu as $d)
                            @include('frontend.noxh.component.pd-similar', ['d' => $d])
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- CỘT PHẢI ---------------------------------------------------- --}}
        <aside class="nx-pd__phu">
            @include('frontend.noxh.component.pd-quick')
            @include('frontend.noxh.component.pd-advisors')
            @include('frontend.noxh.component.pd-lead')
        </aside>
    </div>
</div>

{{-- HỘP BẬT LÊN ------------------------------------------------------- --}}
@if($tatCaTienDo->count())
    <div class="nx-hop" id="tien-do-day" hidden>
        <div class="nx-hop__nen" data-nx-dong></div>
        <div class="nx-hop__khung" role="dialog" aria-modal="true" aria-labelledby="tien-do-day-tieude">
            <div class="nx-hop__dau">
                <h2 id="tien-do-day-tieude">
                    {{ strtr((string) ($intro['projectdetail_progress_modal_heading'] ?? 'TOÀN BỘ TIẾN ĐỘ DỰ ÁN'), $thay) }}
                </h2>
                <button type="button" class="nx-hop__dong" data-nx-dong aria-label="Đóng">
                    @include('frontend.noxh.component.icon', ['name' => 'close', 'size' => 20])
                </button>
            </div>
            <div class="nx-hop__than">
                @include('frontend.noxh.component.pd-progress', [
                    'moc' => $tatCaTienDo, 'coAnh' => true,
                ])
            </div>
        </div>
    </div>
@endif

@if($tatCaNhanVien->count())
    <div class="nx-hop" id="tu-van-day" hidden>
        <div class="nx-hop__nen" data-nx-dong></div>
        <div class="nx-hop__khung" role="dialog" aria-modal="true" aria-labelledby="tu-van-day-tieude">
            <div class="nx-hop__dau">
                <h2 id="tu-van-day-tieude">
                    {{ strtr((string) ($intro['projectlead_staff_modal_heading']
                        ?? ($intro['projectlead_staff_heading'] ?? 'DANH SÁCH TƯ VẤN HỖ TRỢ')), $thay) }}
                </h2>
                <button type="button" class="nx-hop__dong" data-nx-dong aria-label="Đóng">
                    @include('frontend.noxh.component.icon', ['name' => 'close', 'size' => 20])
                </button>
            </div>
            <div class="nx-hop__than">
                <ul class="nx-pd-tuvan__ds nx-pd-tuvan__ds--hop">
                    @foreach($tatCaNhanVien as $nv)
                        @include('frontend.noxh.component.pd-advisor-item', ['nv' => $nv])
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

@include('frontend.noxh.component.advisor-modal', ['duAnId' => $duAn->id])
@endsection
