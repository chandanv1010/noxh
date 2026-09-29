@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/plxh fix.jpg
    //
    // Khong mot chuoi nao duoc viet cung o day - tat ca doc tu bang
    // introduces (nhom "Trang Pháp lý"), mac dinh chi la phao cuu sinh khi
    // quan tri xoa trang mot o.
    $anhNen = trim((string) ($intro['legal_hero_bg'] ?? ''));
    $anhDai = trim((string) ($intro['legal_hero_banner'] ?? ''));

    // Bon o chip: o nao chua dien chu thi bo han, khong de mot o trong.
    $chip = [];

    for ($i = 1; $i <= 4; $i++) {
        $chu = trim((string) ($intro["legal_chip_{$i}_text"] ?? ''));

        if ($chu !== '') {
            $chip[] = ['chu' => $chu, 'icon' => $intro["legal_chip_{$i}_icon"] ?? 'check-circle'];
        }
    }

    // Nam the chu de, cung quy tac: thieu ten thi khong ve the.
    $chuDe = [];

    for ($i = 1; $i <= 5; $i++) {
        $ten = trim((string) ($intro["legal_topic_{$i}_title"] ?? ''));

        if ($ten !== '') {
            $chuDe[] = [
                'ten' => $ten,
                'mo_ta' => $intro["legal_topic_{$i}_description"] ?? '',
                'icon' => $intro["legal_topic_{$i}_icon"] ?? 'scale',
                'link' => trim((string) ($intro["legal_topic_{$i}_link"] ?? '')) ?: '/phap-ly-noxh',
            ];
        }
    }

    // Bon o cam ket cuoi trang.
    $camKet = [];

    for ($i = 1; $i <= 4; $i++) {
        $ten = trim((string) ($intro["legal_trust_{$i}_title"] ?? ''));

        if ($ten !== '') {
            $camKet[] = [
                'ten' => $ten,
                'phu' => $intro["legal_trust_{$i}_sub"] ?? '',
                'icon' => $intro["legal_trust_{$i}_icon"] ?? 'shield-check',
            ];
        }
    }
@endphp

@section('content')
<div class="nx-pl">

    {{-- Dải ảnh nền chỉ là NỀN: tách khỏi luồng nội dung thì thẻ hỗ trợ ở cột
         phải mới trườn được xuống dưới dải, đúng như bản vẽ. --}}
    <div class="nx-pl__nen{{ $anhNen !== '' ? ' co-nen' : '' }}"
         @if($anhNen !== '') style="--nx-nen: url('{{ e($anhNen) }}')" @endif
         aria-hidden="true"></div>

    <div class="nx__container nx-pl__khung">
        @include('frontend.noxh.component.crumb', ['crumbs' => ['Pháp lý NOXH' => '']])

        {{-- Hai cột chạy liền từ đầu trang xuống thân: cột trái là phần giới
             thiệu rồi khối nội dung, cột phải là thẻ hỗ trợ rồi khối văn bản. --}}
        <div class="nx-pl__luoi">
            <div>
                <div class="nx-pl__gioi">
                    @if($anhDai !== '')
                        <img src="{{ $anhDai }}" alt="" class="nx-pl__dai-anh" aria-hidden="true">
                    @endif

                    <div class="nx-pl__ten">
                        @if(!empty($intro['legal_hero_icon']))
                            <span class="nx-pl__ten-icon">
                                @include('frontend.noxh.component.icon', [
                                    'name' => $intro['legal_hero_icon'], 'size' => 58,
                                ])
                            </span>
                        @endif
                        <span>
                            <h1>{{ $intro['legal_heading'] ?? 'PHÒNG PHÁP LÝ NOXH' }}</h1>
                            @if(!empty($intro['legal_description']))
                                <p class="nx-pl__khau-hieu">{{ $intro['legal_description'] }}</p>
                            @endif
                        </span>
                    </div>

                    @if(!empty($intro['legal_intro']))
                        <p class="nx-pl__mo-ta">{!! nl2br(e($intro['legal_intro'])) !!}</p>
                    @endif

                    @if(count($chip))
                        <div class="nx-pl__chips">
                            @foreach($chip as $c)
                                <div class="nx-chip">
                                    @include('frontend.noxh.component.icon', ['name' => $c['icon'], 'size' => 30])
                                    <span>{!! nl2br(e($c['chu'])) !!}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="nx-panel nx-pl__chinh">
                    @if(count($chuDe))
                        <h2 class="nx-pl__tieude">
                            {{ $intro['legal_topic_heading'] ?? 'CHỦ ĐỀ PHÁP LÝ NOXH' }}
                        </h2>

                        <div class="nx-pl-topics">
                            @foreach($chuDe as $cd)
                                <a href="{{ url($cd['link']) }}" class="nx-topic">
                                    <span class="nx-topic__icon">
                                        @include('frontend.noxh.component.icon', ['name' => $cd['icon'], 'size' => 36])
                                    </span>
                                    <strong>{{ $cd['ten'] }}</strong>
                                    @if($cd['mo_ta'])<p>{{ $cd['mo_ta'] }}</p>@endif
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <h2 class="nx-pl__tieude">
                        {{ $intro['legal_post_heading'] ?? 'BÀI VIẾT PHÁP LÝ MỚI NHẤT' }}
                        <a href="{{ url('/tin-tuc') }}">{{ $intro['legal_post_all_text'] ?? 'Xem tất cả' }}</a>
                    </h2>

                    @if($baiViet->count())
                        <div class="nx-pl-posts">
                            @foreach($baiViet as $bai)
                                @include('frontend.noxh.component.legal-post', ['bai' => $bai])
                            @endforeach
                        </div>
                    @else
                        <p class="nx-pl__trong">{{ $intro['legal_post_empty'] ?? 'Chưa có bài viết nào.' }}</p>
                    @endif

                    @if(!empty($intro['legal_cta_title']))
                        <div class="nx-pl-cta">
                            @if(!empty($intro['legal_cta_icon']))
                                <span class="nx-pl-cta__icon">
                                    @include('frontend.noxh.component.icon', [
                                        'name' => $intro['legal_cta_icon'], 'size' => 28,
                                    ])
                                </span>
                            @endif

                            <div class="nx-pl-cta__chu">
                                <strong>{{ $intro['legal_cta_title'] }}</strong>
                                @if(!empty($intro['legal_cta_description']))
                                    <span>{{ $intro['legal_cta_description'] }}</span>
                                @endif
                            </div>

                            @if(!empty($intro['legal_cta_button']))
                                <a href="{{ url(trim((string) ($intro['legal_cta_link'] ?? '')) ?: '/hoi-dap') }}"
                                   class="nx-pl-cta__nut">
                                    {{ $intro['legal_cta_button'] }}
                                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 17])
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <aside class="nx-pl__ben">
                @include('frontend.noxh.component.legal-help')

                <div class="nx-panel nx-pl__van-ban">
                    <h2 class="nx-pl__tieude">
                        {{ $intro['legal_doc_heading'] ?? 'VĂN BẢN PHÁP LUẬT MỚI' }}
                        <a href="{{ url('/phap-ly-noxh/van-ban') }}">
                            {{ $intro['legal_doc_all_text'] ?? 'Xem tất cả' }}
                            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
                        </a>
                    </h2>

                    @forelse($vanBan as $vb)
                        @include('frontend.noxh.component.legal-doc-item', ['vb' => $vb])
                    @empty
                        <p class="nx-pl__trong">{{ $intro['legal_doc_empty'] ?? 'Chưa có văn bản nào.' }}</p>
                    @endforelse
                </div>
            </aside>
        </div>

        {{-- DẢI CAM KẾT CUỐI TRANG --}}
        @if(count($camKet))
            <div class="nx-pl-trust">
                @foreach($camKet as $ck)
                    <div class="nx-pl-trust__o">
                        @include('frontend.noxh.component.icon', ['name' => $ck['icon'], 'size' => 34])
                        <span>
                            <strong>{{ $ck['ten'] }}</strong>
                            @if($ck['phu'])<em>{{ $ck['phu'] }}</em>@endif
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
