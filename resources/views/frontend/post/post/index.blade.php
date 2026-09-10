@extends('frontend.homepage.layout')

@section('content')
    <!-- Khối Đầu Trang Chủ (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <div id="art-detail" class="page-body">
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
                    <li><a href="#" onclick="return false;" class="active-crumb">{{ $post->name }}</a></li>
                </ul>
            </div>

            @if($postCatalogue->canonical === 'du-an-tieu-bieu')
                {{-- PROJECT DETAIL LAYOUT SLIDER PART --}}
                <div class="project-detail-slider-section uk-margin-bottom">
                    @php
                        $albumSource = is_array($post->album) ? $post->album : json_decode($post->album ?? '[]', true);
                        $list_image = array_values(array_filter(is_array($albumSource) ? $albumSource : []));
                        if (!empty($post->image)) {
                            array_unshift($list_image, $post->image);
                        }
                        $list_image = array_values(array_unique($list_image));
                    @endphp
                    @if(count($list_image))
                        <!-- Main Viewer -->
                        <div class="project-main-viewer-wrapper">
                            <div class="project-main-image-container">
                                <img id="project-main-image" src="{{ asset($list_image[0]) }}" alt="{{ $post->name }}">
                            </div>
                            
                            <!-- Thumbs slider row -->
                            <div class="project-thumbs-container uk-flex uk-flex-middle uk-margin-top">
                                @foreach($list_image as $idx => $img)
                                    <div class="project-thumb-item {{ $idx === 0 ? 'active' : '' }}" onclick="switchProjectImage('{{ asset($img) }}', this)">
                                        <img src="{{ asset($img) }}" alt="">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Main Layout: 3/4 content, 1/4 sidebar --}}
            <div class="uk-grid uk-grid-large uk-margin-large-top" data-uk-grid-margin>
                <!-- CỘT TRÁI (3/4): Chi tiết bài viết -->
                <div class="uk-width-large-3-4 uk-width-medium-1-1 uk-width-small-1-1">
                    <div class="art-detail-card">
                        <h1 class="art-title">{{ $post->name }}</h1>
                        
                        <div class="art-meta uk-flex uk-flex-middle">
                            <span class="meta-item"><i class="fa fa-calendar"></i> {{ \Carbon\Carbon::parse($post->created_at)->format('d/m/Y H:i') }}</span>
                            <span class="meta-item"><i class="fa fa-eye"></i> {{ $post->viewed }} lượt xem</span>
                        </div>

                        {{-- If it is a video, embed the video player --}}
                        @if($postCatalogue->canonical === 'video-tieu-bieu' && !empty($post->video))
                            <div class="video-detail-player uk-margin-large-bottom">
                                <div class="video-iframe-container">
                                    {!! $post->video !!}
                                </div>
                            </div>
                        @endif

                        {{-- If it is a project, render the project details --}}
                        @if($postCatalogue->canonical === 'du-an-tieu-bieu')
                            <div class="project-info-card uk-margin-large-bottom">
                                <h3 class="info-card-title">Thông tin dự án</h3>
                                <div class="info-card-content editor-style">
                                    {!! $post->description !!}
                                </div>
                            </div>
                        @endif

                        @if($postCatalogue->canonical !== 'du-an-tieu-bieu')
                            <div class="art-desc uk-text-lead">
                                {!! $post->description !!}
                            </div>
                        @endif

                        <div class="art-content-body editor-style uk-margin-large-bottom">
                            {!! $post->content !!}
                        </div>

                        <!-- Bài viết liên quan -->
                        @if (isset($postCatalogue->posts) && !is_null($postCatalogue->posts))
                            <div class="related-posts-block uk-margin-large-top">
                                <h3 class="related-block-title">BÀI VIẾT LIÊN QUAN</h3>
                                <div class="uk-grid uk-grid-medium uk-grid-width-1-1 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3" data-uk-grid-margin>
                                    @php $count = 0; @endphp
                                    @foreach ($postCatalogue->posts as $key => $val)
                                        @php
                                            if($val->id === $post->id) continue; 
                                            if(++$count > 3) break;
                                            $title = $val->languages->first()->pivot->name;
                                            $image = $val->image;
                                            $href  = write_url($val->languages->first()->pivot->canonical);
                                        @endphp
                                        <div class="uk-margin-bottom">
                                            <div class="related-post-card hover-animate">
                                                <a href="{{ $href }}" class="card-thumb">
                                                    <img src="{{ asset($image) }}" alt="{{ $title }}">
                                                </a>
                                                <h4 class="card-title">
                                                    <a href="{{ $href }}" title="{{ $title }}">{{ $title }}</a>
                                                </h4>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- CỘT PHẢI (1/4): Sidebar Widgets -->
                <div class="uk-width-large-1-4 uk-width-medium-1-1 uk-width-small-1-1">
                    <div class="sidebar-detail">
                        <!-- DỰ ÁN NỔI BẬT widget -->
                        @php
                            $featuredProjectWidget = $widgets['featured-project'] ?? null;
                            $projectCat = (isset($featuredProjectWidget->object) && $featuredProjectWidget->object->isNotEmpty()) ? $featuredProjectWidget->object->first() : null;
                            $projectPosts = $projectCat ? $projectCat->posts->take(5) : collect();
                        @endphp
                        @if($projectPosts->isNotEmpty())
                            <div class="sidebar-widget uk-margin-large-bottom">
                                <h3 class="widget-title">DỰ ÁN NỔI BẬT</h3>
                                <ul class="uk-list widget-posts-list">
                                    @foreach($projectPosts as $wPost)
                                        @php
                                            $wLang = $wPost->languages->first();
                                        @endphp
                                        <li class="uk-flex uk-flex-top widget-post-item">
                                            <a href="{{ write_url($wLang->canonical) }}" class="widget-post-thumb">
                                                <img src="{{ asset($wPost->image) }}" alt="{{ $wLang->name }}">
                                            </a>
                                            <div class="widget-post-info">
                                                <h4 class="post-title">
                                                    <a href="{{ write_url($wLang->canonical) }}">{{ $wLang->name }}</a>
                                                </h4>
                                                <div class="post-date">
                                                    {{ \Carbon\Carbon::parse($wPost->created_at)->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- TIN TỨC NỔI BẬT widget -->
                        @php
                            $newsWidget = $widgets['homepage-news'] ?? null;
                            $newsCat = (isset($newsWidget->object) && $newsWidget->object->isNotEmpty()) ? $newsWidget->object->first() : null;
                            $newsPosts = $newsCat ? $newsCat->posts->take(5) : collect();
                        @endphp
                        @if($newsPosts->isNotEmpty())
                            <div class="sidebar-widget uk-margin-large-bottom">
                                <h3 class="widget-title">TIN TỨC NỔI BẬT</h3>
                                <ul class="uk-list widget-posts-list">
                                    @foreach($newsPosts as $wPost)
                                        @php
                                            $wLang = $wPost->languages->first();
                                        @endphp
                                        <li class="uk-flex uk-flex-top widget-post-item">
                                            <a href="{{ write_url($wLang->canonical) }}" class="widget-post-thumb">
                                                <img src="{{ asset($wPost->image) }}" alt="{{ $wLang->name }}">
                                            </a>
                                            <div class="widget-post-info">
                                                <h4 class="post-title">
                                                    <a href="{{ write_url($wLang->canonical) }}">{{ $wLang->name }}</a>
                                                </h4>
                                                <div class="post-date">
                                                    {{ \Carbon\Carbon::parse($wPost->created_at)->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to switch project images -->
    <script>
        function switchProjectImage(src, element) {
            document.getElementById('project-main-image').src = src;
            const thumbs = document.querySelectorAll('.project-thumb-item');
            thumbs.forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }
    </script>

    <style>
        /* ===== ARTICLE DETAIL PAGE CUSTOM STYLES ===== */
        #art-detail {
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

        /* Project gallery details */
        .project-main-viewer-wrapper {
            border: 4px solid #000000;
            border-radius: 12px;
            background: #ffffff;
            padding: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }
        .project-main-image-container {
            width: 100%;
            height: 480px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .project-main-image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .project-thumbs-container {
            gap: 12px;
            overflow-x: auto;
            padding-bottom: 8px;
        }
        .project-thumb-item {
            width: 80px;
            height: 80px;
            border: 3px solid #000000;
            border-radius: 6px;
            overflow: hidden;
            cursor: pointer;
            background: #ffffff;
            opacity: 0.6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            box-sizing: border-box;
            flex-shrink: 0;
        }
        .project-thumb-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        .project-thumb-item:hover, .project-thumb-item.active {
            opacity: 1;
            border-color: #ff2a2a;
        }

        /* Article detail card */
        .art-detail-card {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            box-sizing: border-box;
        }
        .art-title {
            font-size: 28px;
            font-weight: 950;
            color: #000000;
            line-height: 1.3;
            margin: 0 0 15px 0;
            text-transform: uppercase;
        }
        .art-meta {
            gap: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
        }
        .meta-item {
            font-size: 13.5px;
            font-weight: 850;
            color: #555555;
        }
        .meta-item i {
            color: #000000;
            margin-right: 4px;
        }
        .art-desc {
            font-size: 16px;
            line-height: 1.8;
            font-weight: 700;
            color: #000000;
            margin-bottom: 30px;
            padding-left: 15px;
            border-left: 4px solid #ff2a2a;
        }

        /* Video details */
        .video-iframe-container {
            position: relative;
            padding-bottom: 56.25%;
            height: 0;
            overflow: hidden;
            background: #000000;
            border: 3px solid #000000;
            border-radius: 8px;
        }
        .video-iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Project Info Card */
        .project-info-card {
            background: #fff9e6;
            border: 3px solid #000000;
            padding: 25px;
            border-radius: 8px;
        }
        .info-card-title {
            font-size: 18px;
            font-weight: 900;
            color: #000000;
            text-transform: uppercase;
            border-bottom: 2px solid #000000;
            padding-bottom: 10px;
            margin: 0 0 15px 0;
        }

        /* Content Body / CKEditor Styles */
        .editor-style {
            font-size: 15px;
            line-height: 1.8;
            color: #222222;
        }
        .editor-style h2, .editor-style h3 {
            font-weight: 900;
            color: #000000;
            margin-top: 25px;
        }
        .editor-style img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
            border: 3px solid #000000;
        }
        .editor-style p {
            margin-bottom: 15px;
        }

        /* Sidebar Widgets */
        .sidebar-widget {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            box-sizing: border-box;
        }
        .widget-title {
            font-size: 18px;
            font-weight: 950;
            color: #000000;
            text-transform: uppercase;
            border-bottom: 3px solid #000000;
            padding-bottom: 10px;
            margin: 0 0 20px 0;
            letter-spacing: 0.5px;
        }
        .widget-posts-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 0;
            padding: 0;
        }
        .widget-post-item {
            gap: 12px;
        }
        .widget-post-thumb {
            width: 70px;
            height: 70px;
            border: 2px solid #000000;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }
        .widget-post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .widget-post-info {
            min-width: 0;
        }
        .widget-post-info .post-title {
            font-size: 13px;
            font-weight: 800;
            line-height: 1.4;
            margin: 0 0 5px 0;
        }
        .widget-post-info .post-title a {
            color: #000000;
            text-decoration: none;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .widget-post-info .post-title a:hover {
            color: #ff2a2a;
        }
        .widget-post-info .post-date {
            font-size: 11px;
            font-weight: 700;
            color: #888888;
        }

        /* Related block titles */
        .related-posts-block {
            border-top: 2px dashed #000000;
            padding-top: 30px;
        }
        .related-block-title {
            font-size: 18px;
            font-weight: 950;
            color: #000000;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .related-post-card {
            background: transparent;
            border: none;
            border-radius: 0;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .related-post-card .card-thumb {
            width: 100%;
            height: 140px;
            overflow: hidden;
            display: block;
            border-bottom: none;
        }
        .related-post-card .card-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.25s;
        }
        .related-post-card:hover .card-thumb img {
            transform: scale(1.05);
        }
        .related-post-card .card-title {
            margin: 10px;
            font-size: 13px;
            font-weight: 900;
            line-height: 1.4;
            height: 36px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .related-post-card .card-title a {
            color: #000000;
            text-decoration: none;
        }
        .related-post-card .card-title a:hover {
            color: #ff2a2a;
        }
        .hover-animate:hover {
            transform: translateY(-5px);
            border-color: #ff2a2a;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        /* Responsive styles */
        @media (max-width: 767px) {
            .art-detail-card {
                padding: 20px;
            }
            .art-title {
                font-size: 22px;
            }
            .project-main-image-container {
                height: 300px;
            }
        }
    </style>
@endsection
