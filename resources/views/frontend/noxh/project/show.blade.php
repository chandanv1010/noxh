@extends('frontend.noxh.layout')

@section('content')
@php
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

    // Thanh tab chi liet ke khoi CO du lieu. Them mot khoi moi thi them mot
    // dong o day, khong phai sua cho nao khac.
    $khoi = [
        ['id' => 'tong-quan', 'ma' => 'overview', 'icon' => 'grid', 'co' => count($bangTongQuan)],
        ['id' => 'mat-bang', 'ma' => 'units', 'icon' => 'floor-plan', 'co' => $loaiCanHo->count()],
        ['id' => 'vi-tri', 'ma' => 'location', 'icon' => 'pin', 'co' => !empty($duAn->map_image) || $banDoUrl],
        ['id' => 'tien-ich', 'ma' => 'amenity', 'icon' => 'bulb-rays', 'co' => $tienIch->count()],
        ['id' => 'tien-do', 'ma' => 'progress', 'icon' => 'update', 'co' => $tienDo->count()],
        ['id' => 'phap-ly', 'ma' => 'legal', 'icon' => 'file', 'co' => $hoSo->count()],
        ['id' => 'hinh-anh', 'ma' => 'gallery', 'icon' => 'photo', 'co' => count($album)],
        ['id' => 'gioi-thieu', 'ma' => 'content', 'icon' => 'news', 'co' => !empty($duAn->content)],
        ['id' => 'hoi-dap', 'ma' => 'faq', 'icon' => 'question', 'co' => $faq->count()],
    ];

    $tab = [];
    $tieuDe = [];

    foreach ($khoi as $k) {
        $tieuDe[$k['ma']] = strtr((string) ($intro['projectdetail_' . $k['ma'] . '_heading'] ?? ''), $thay);

        if ($k['co']) {
            $tab[] = [
                'id' => $k['id'],
                'icon' => $k['icon'],
                'ten' => $intro['projectdetail_' . $k['ma'] . '_tab'] ?? $tieuDe[$k['ma']],
            ];
        }
    }
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

    @include('frontend.noxh.component.pd-tabs', ['tab' => $tab])

    <div class="nx__container nx-pd__than">

        {{-- CỘT TRÁI ---------------------------------------------------- --}}
        <div class="nx-pd__chinh">

            {{-- TỔNG QUAN --}}
            @if(count($bangTongQuan))
                <section class="nx-panel nx-pd-tq" id="tong-quan">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['overview'] ?: 'TỔNG QUAN DỰ ÁN' }}</h2>

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
                                    <a href="#hinh-anh" class="nx-pd-tq__nut">
                                        @include('frontend.noxh.component.icon', ['name' => 'photo', 'size' => 18])
                                        {{ $intro['projectdetail_overview_photo_text'] ?? 'Xem ảnh thực tế' }}
                                    </a>
                                @endif
                            </figure>
                        @endif
                    </div>
                </section>
            @endif

            {{-- CÁC LOẠI CĂN HỘ --}}
            @if($loaiCanHo->count())
                <section class="nx-panel" id="mat-bang">
                    <h2 class="nx-pd__tieude nx-pd__tieude--co-nut">
                        {{ $tieuDe['units'] ?: 'CÁC LOẠI CĂN HỘ' }}
                        @if($conLoaiCanHo > 0)
                            <a href="#dang-ky">
                                {{ $intro['projectdetail_units_all_text'] ?? 'Xem tất cả' }}
                                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
                            </a>
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
            @if((!empty($duAn->map_image) || $banDoUrl) || $tienDo->count())
                <div class="nx-pd-doi">
                    @if(!empty($duAn->map_image) || $banDoUrl)
                        <section class="nx-panel nx-pd-vitri" id="vi-tri">
                            <h2 class="nx-pd__tieude">{{ $tieuDe['location'] ?: 'VỊ TRÍ DỰ ÁN' }}</h2>

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
                            <h2 class="nx-pd__tieude">{{ $tieuDe['progress'] ?: 'TIẾN ĐỘ DỰ ÁN' }}</h2>

                            <div class="nx-pd-tiendo__trong">
                                <ol class="nx-pd-moc">
                                    @foreach($tienDo as $moc)
                                        <li class="{{ $moc->status === 'done' ? 'is-xong' : ($moc->status === 'doing' ? 'is-lam' : '') }}">
                                            <span class="nx-pd-moc__dau">
                                                @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 18])
                                            </span>
                                            <span class="nx-pd-moc__chu">
                                                <strong>{{ $moc->date_label ?: nx_quy_nam($moc->sort_date) }}</strong>
                                                <span>{{ $moc->title }}</span>
                                            </span>
                                        </li>
                                    @endforeach
                                </ol>

                                @if(!empty($duAn->progress_image))
                                    <figure class="nx-pd-tiendo__anh">
                                        <img src="{{ $duAn->progress_image }}"
                                             alt="Hình ảnh thi công {{ $duAn->name }}" loading="lazy" decoding="async">
                                    </figure>
                                @endif
                            </div>

                            @if(!empty($duAn->progress_url))
                                <a href="{{ nx_url($duAn->progress_url) }}" class="nx-pd-tiendo__nut">
                                    @include('frontend.noxh.component.icon', ['name' => 'update', 'size' => 18])
                                    {{ $intro['projectdetail_progress_button'] ?? 'Xem cập nhật tiến độ' }}
                                </a>
                            @endif
                        </section>
                    @endif
                </div>
            @endif

            {{-- TIỆN ÍCH --}}
            @if($tienIch->count())
                <section class="nx-panel" id="tien-ich">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['amenity'] ?: 'TIỆN ÍCH DỰ ÁN' }}</h2>

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

            {{-- PHÁP LÝ --}}
            @if($hoSo->count())
                <section class="nx-panel" id="phap-ly">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['legal'] ?: 'PHÁP LÝ DỰ ÁN' }}</h2>
                    <div class="nx-doc-grid">
                        @foreach($hoSo as $hs)
                            <a href="{{ $hs->file ?: '#' }}" class="nx-doc" @if($hs->file) target="_blank" rel="noopener" @endif>
                                <span class="nx-doc__icon">
                                    @include('frontend.noxh.component.icon', ['name' => 'file', 'size' => 22])
                                </span>
                                <span>
                                    <strong>{{ $hs->title }}</strong>
                                    @if($hs->doc_number)<span>Số: {{ $hs->doc_number }}</span>@endif
                                    @if($hs->issued_date)<span>Ngày: {{ \Illuminate\Support\Carbon::parse($hs->issued_date)->format('d/m/Y') }}</span>@endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- HÌNH ẢNH --}}
            @if(count($album))
                <section class="nx-panel" id="hinh-anh">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['gallery'] ?: 'HÌNH ẢNH DỰ ÁN' }}</h2>
                    <div class="nx-pd-album">
                        @foreach($album as $a)
                            <a href="{{ $a }}" target="_blank" rel="noopener">
                                <img src="{{ $a }}" alt="{{ $duAn->name }}" loading="lazy" decoding="async">
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- GIỚI THIỆU CHI TIẾT --}}
            @if($duAn->content)
                <section class="nx-panel" id="gioi-thieu">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['content'] ?: 'GIỚI THIỆU CHI TIẾT' }}</h2>
                    <div class="nx-prose">{!! $duAn->content !!}</div>
                </section>
            @endif

            {{-- HỎI ĐÁP --}}
            @if($faq->count())
                <section class="nx-panel" id="hoi-dap">
                    <h2 class="nx-pd__tieude">{{ $tieuDe['faq'] ?: 'CÂU HỎI THƯỜNG GẶP' }}</h2>
                    @foreach($faq as $ch)
                        <details class="nx-faq">
                            <summary>{{ $ch->question }}</summary>
                            <div class="nx-faq__body">{!! nl2br(e(strip_tags($ch->answer))) !!}</div>
                        </details>
                    @endforeach
                </section>
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

@include('frontend.noxh.component.advisor-modal', ['duAnId' => $duAn->id])
@endsection
