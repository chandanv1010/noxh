@extends('frontend.homepage.layout')

@section('content')
    <!-- Khối Đầu Trang (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <div id="art-catalogue" class="page-body">
        <div class="uk-container uk-container-center page-container">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs-row uk-margin-bottom">
                <ul class="uk-list uk-clearfix uk-flex uk-flex-middle cat-hero-breadcrumbs">
                    <li><a href="/">Trang chủ</a></li>
                    @if(!is_null($breadcrumb))
                        @foreach($breadcrumb as $key => $val)
                            @php
                                $name = $val->languages->first()->pivot->name;
                                $canonical = write_url($val->languages->first()->pivot->canonical, true, true);
                            @endphp
                            <li class="separator"><i class="fa fa-angle-right"></i></li>
                            <li><a href="{{ $canonical }}">{{ $name }}</a></li>
                        @endforeach
                    @endif
                    <li class="separator"><i class="fa fa-angle-right"></i></li>
                    <li><a href="#" onclick="return false;" class="active-crumb">{{ $postCatalogue->name }}</a></li>
                </ul>
            </div>

            <!-- Page Title -->
            <div class="page-header-row uk-margin-large-bottom">
                <h1 class="page-title">{{ $postCatalogue->name }}</h1>
            </div>

            <div class="art-catalogue-wrapper">
                @if($postCatalogue->canonical === 'video-tieu-bieu')
                    {{-- Videos List Style --}}
                    <div class="uk-grid uk-grid-large uk-grid-width-1-2 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3" data-uk-grid-margin>
                        @foreach($posts as $key => $val)
                            @php
                                $title = $val->languages->first()->pivot->name;
                                $image = $val->image;
                                $href = write_url($val->languages->first()->pivot->canonical);
                            @endphp
                            <div class="uk-margin-bottom">
                                <div class="premium-card video-card hover-animate">
                                    <div class="card-thumb-wrapper">
                                        <a href="{{ $href }}" class="card-image-link">
                                            <img src="{{ asset($image) }}" alt="{{ $title }}">
                                            <div class="play-overlay">
                                                <i class="fa fa-play-circle"></i>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="card-body">
                                        <h3 class="card-title">
                                            <a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a>
                                        </h3>
                                        <div class="card-meta">
                                            <span><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($val->created_at)->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- News & General Posts List Style --}}
                    <div class="uk-grid uk-grid-large uk-grid-width-1-2 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3" data-uk-grid-margin>
                        @foreach($posts as $key => $val)
                            @php
                                $title = $val->languages->first()->pivot->name;
                                $image = $val->image;
                                $href = write_url($val->languages->first()->pivot->canonical);
                                $description = cutnchar(strip_tags($val->languages->first()->pivot->description), 120);
                            @endphp
                            <div class="uk-margin-bottom">
                                <div class="premium-card news-card hover-animate">
                                    <a href="{{ $href }}" class="card-image-link">
                                        <img src="{{ asset($image) }}" alt="{{ $title }}" class="card-img">
                                    </a>
                                    <div class="card-body">
                                        <div class="date-tag">
                                            <i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($val->created_at)->format('d/m/Y') }}
                                        </div>
                                        <h3 class="card-title">
                                            <a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a>
                                        </h3>
                                        <div class="card-desc">
                                            {!! $description !!}
                                        </div>
                                        <a href="{{ $href }}" class="btn-read-more">Xem chi tiết <i class="fa fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                <div class="uk-flex uk-flex-center uk-margin-large-top">
                    @include('frontend.component.pagination', ['model' => $posts])
                </div>
            </div>
        </div>
    </div>

    <style>
        /* ===== ARTICLE CATALOGUE PAGE STYLES ===== */
        #art-catalogue {
            background-color: #ffc600; /* Nền vàng đặc trưng đồng bộ */
            padding: 40px 0 60px 0;
            font-family: var(--second-font), sans-serif;
            box-sizing: border-box;
        }

        .page-container {
            box-sizing: border-box;
        }

        /* Breadcrumbs */
        .cat-hero-breadcrumbs {
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 8px;
        }
        .cat-hero-breadcrumbs li, .cat-hero-breadcrumbs li a {
            font-size: 13.5px;
            color: #000000;
            font-weight: 800;
            text-decoration: none;
        }
        .cat-hero-breadcrumbs li a:hover {
            color: #ff2a2a;
        }
        .cat-hero-breadcrumbs li.separator {
            color: #000000;
            font-weight: 900;
        }
        .cat-hero-breadcrumbs li .active-crumb {
            color: #555555;
            cursor: default;
            pointer-events: none;
        }

        /* Title */
        .page-title {
            font-size: 32px;
            font-weight: 950;
            color: #000000;
            margin: 20px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Premium Cards */
        .premium-card {
            background: transparent;
            border: 3px solid #000000;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 420px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-sizing: border-box;
        }
        .card-image-link {
            width: 100%;
            height: 180px;
            overflow: hidden;
            display: block;
            position: relative;
            background: transparent;
            border-bottom: 3px solid #000000;
        }
        .card-image-link img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .premium-card:hover .card-image-link img {
            transform: scale(1.05);
        }
        .card-body {
            padding: 18px;
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
        }
        .date-tag {
            font-size: 11.5px;
            font-weight: 700;
            color: #ff2a2a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .card-title {
            margin: 0 0 10px 0;
            font-size: 15px;
            font-weight: 900;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 42px;
        }
        .card-title a {
            color: #000000;
            text-decoration: none;
            transition: color 0.2s;
        }
        .card-title a:hover {
            color: #ff2a2a;
        }
        .card-desc {
            font-size: 13px;
            color: #444444;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }
        .btn-read-more {
            font-size: 12.5px;
            font-weight: 850;
            color: #000000 !important;
            text-decoration: none !important;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: gap 0.2s;
            align-self: flex-start;
        }
        .btn-read-more:hover {
            color: #ff2a2a !important;
            gap: 10px;
        }

        /* Video specific card styles */
        .video-card .card-thumb-wrapper {
            position: relative;
        }
        .play-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.8;
            transition: opacity 0.3s;
        }
        .play-overlay i {
            font-size: 54px;
            color: #ffffff;
            transition: transform 0.3s;
        }
        .premium-card:hover .play-overlay {
            opacity: 1;
            background: rgba(0,0,0,0.1);
        }
        .premium-card:hover .play-overlay i {
            transform: scale(1.15);
            color: #ff2a2a;
        }
        .video-card .card-meta {
            font-size: 12px;
            font-weight: 700;
            color: #555555;
            margin-top: auto;
        }

        /* Hover animations */
        .hover-animate:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
            border-color: #ff2a2a;
        }
    </style>
@endsection
