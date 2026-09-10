@extends('frontend.homepage.layout')

@section('content')
<div class="about-wrapper">
    <!-- Section 1: Hero Banner -->
    <div class="about-hero cat-hero-section" style="background-image: url('/vendor/frontend/img/project/breadcrumb.png');">
        <div class="cat-hero-overlay"></div>
        <div class="uk-container uk-container-center cat-hero-container">
            <h1 class="cat-hero-title">Về Chúng Tôi</h1>
            <ul class="uk-list uk-clearfix uk-flex uk-flex-middle uk-flex-center cat-hero-breadcrumbs">
                <li><a href="/">Trang chủ</a></li>
                <li class="separator">»</li>
                <li><a href="#" onclick="return false;">Về Chúng Tôi</a></li>
            </ul>
        </div>
    </div>

    <!-- Section 2: Giới thiệu chung -->
    @php
        $aboutWidget = $widgets['about-us'] ?? null;
        $aboutCat = (isset($aboutWidget->object) && $aboutWidget->object->isNotEmpty()) ? $aboutWidget->object->first() : null;
        $aboutPost = ($aboutCat && $aboutCat->posts->isNotEmpty()) ? $aboutCat->posts->first() : null;
        
        $aboutTitle = 'GIỚI THIỆU VỀ CHÚNG TÔI';
        $aboutHighlight = 'Tazen là đơn vị chuyên cung cấp lavabo, vòi sen và thiết bị phòng tắm hiện đại, bền đẹp, phù hợp cho nhà ở, căn hộ, khách sạn và công trình. Chúng tôi mang đến giải pháp tối ưu cho không gian sống của bạn.';
        $aboutContent = '<p>Với triết lý đặt chất lượng sản phẩm và trải nghiệm người dùng lên hàng đầu, Tazen luôn nghiên cứu và tuyển chọn kỹ lưỡng những chất liệu bền bỉ, công nghệ tiết kiệm nước tiên tiến cùng mẫu mã đa dạng, đón đầu xu hướng thiết kế toàn cầu.</p><p>Sứ mệnh của chúng tôi là kiến tạo nên không gian phòng tắm tiện nghi, thư giãn tuyệt đối cho mọi gia đình Việt, song hành cùng dịch vụ bảo hành và chăm sóc khách hàng chuyên nghiệp, tận tâm nhất.</p>';
        $aboutMainImage = '/vendor/frontend/img/project/tazen/project_1.png';
        $aboutSubImage = '/vendor/frontend/img/project/tazen/project_2.png';

        if ($aboutPost) {
            $postLang = $aboutPost->languages->first();
            if ($postLang) {
                $aboutTitle = $postLang->name;
                $aboutHighlight = strip_tags($postLang->description);
                $aboutContent = $postLang->content;
            }
            $aboutMainImage = $aboutPost->image;
            $album = json_decode($aboutPost->album, true);
            if (!empty($album) && is_array($album)) {
                $aboutSubImage = $album[0];
            }
        }
    @endphp

    <div class="about-intro-section">
        <div class="uk-container uk-container-center">
            <div class="uk-grid uk-grid-large uk-flex-middle" data-uk-grid-margin>
                <!-- Left text column -->
                <div class="uk-width-large-1-2 uk-width-1-1 text-col">
                    <span class="sub-title">— Về chúng tôi</span>
                    <h2 class="section-title">{!! html_entity_decode($aboutTitle) !!}</h2>
                    <div class="text-content">
                        @if($aboutHighlight)
                            <p class="p-highlight">
                                {!! html_entity_decode($aboutHighlight) !!}
                            </p>
                        @endif
                        {!! html_entity_decode($aboutContent) !!}
                    </div>
                </div>

                <!-- Right image column -->
                <div class="uk-width-large-1-2 uk-width-1-1 image-col">
                    <div class="about-featured-image">
                        <img src="{{ $aboutMainImage }}" alt="{{ $aboutTitle }}">
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Section 3: Dự án nổi bật -->
    @php
        $projectWidget = $widgets['featured-project'] ?? null;
        $projectCat = (isset($projectWidget->object) && $projectWidget->object->isNotEmpty()) ? $projectWidget->object->first() : null;
        $projectPosts = $projectCat ? $projectCat->posts : collect();
    @endphp

    @if(false && $projectCat && $projectPosts->isNotEmpty())
    <div class="panel-featured-projects">
        <div class="uk-container uk-container-center">
            <div class="project-header">
                <span class="tag">— Dự án</span>
                <h2 class="title">DỰ ÁN NỔI BẬT</h2>
            </div>
        </div>
        <div class="swiper-container project-swiper-about">
            <div class="swiper-wrapper">
                @foreach($projectPosts as $post)
                    @php
                        $lang = $post->languages->first();
                    @endphp
                    <div class="swiper-slide project-slide-item">
                        <div class="project-card">
                            <a href="{{ write_url($lang->canonical) }}" class="card-image">
                                <img src="{{ $post->image }}" alt="{{ $lang->name }}">
                            </a>
                            <div class="card-body">
                                <div class="project-info-list">
                                    {!! html_entity_decode($lang->description) !!}
                                </div>
                                <a href="{{ write_url($lang->canonical) }}" class="btn-xem-them">XEM THÊM</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Section 4: Tin tức nổi bật -->
    @php
        $newsWidget = $widgets['homepage-news'] ?? null;
        $newsCat = (isset($newsWidget->object) && $newsWidget->object->isNotEmpty()) ? $newsWidget->object->first() : null;
        $newsPosts = $newsCat ? $newsCat->posts : collect();
    @endphp

    @if($newsCat && $newsPosts->isNotEmpty())
    <div class="about-news-section">
        <div class="uk-container uk-container-center">
            <div class="uk-grid uk-grid-large" data-uk-grid-margin>
                <!-- Left text column -->
                <div class="uk-width-large-1-3 uk-width-1-1 news-left-col">
                    <span class="sub-title">— Tin tức</span>
                    <h2 class="section-title">Tin Tức Nổi Bật</h2>
                    <p class="section-desc">
                        Cập nhật những xu hướng thiết kế phòng tắm mới nhất, bí quyết chọn mua và hướng dẫn bảo quản thiết bị vệ sinh từ các chuyên gia Tazen.
                    </p>
                </div>

                <!-- Right slider column -->
                <div class="uk-width-large-2-3 uk-width-1-1 news-right-col">
                    <div class="swiper-container about-news-swiper">
                        <div class="swiper-wrapper">
                            @foreach($newsPosts as $post)
                                @php
                                    $lang = $post->languages->first();
                                    $postDate = \Carbon\Carbon::parse($post->created_at)->format('Y-m-d H:i:s');
                                @endphp
                                <div class="swiper-slide news-slide-item">
                                    <a href="{{ write_url($lang->canonical) }}" class="news-card-new">
                                        <div class="card-image-wrapper">
                                            <img src="{{ $post->image }}" alt="{{ $lang->name }}">
                                        </div>
                                        <div class="card-content">
                                            <h3 class="card-title">{!! html_entity_decode($lang->name) !!}</h3>
                                            <span class="card-date"><i class="fa fa-clock-o"></i> {{ $postDate }}</span>
                                            <p class="card-desc">{!! cutnchar(strip_tags(html_entity_decode($lang->description)), 120) !!}</p>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination news-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section 5: Cảm nhận khách hàng -->
    @php
        $feedbackWidget = $widgets['feedback'] ?? null;
        $feedbackCat = (isset($feedbackWidget->object) && $feedbackWidget->object->isNotEmpty()) ? $feedbackWidget->object->first() : null;
        $feedbackPosts = $feedbackCat ? $feedbackCat->posts : collect();
    @endphp

    @if(false && $feedbackCat && $feedbackPosts->isNotEmpty())
    <div class="about-testimonials-section">
        <div class="uk-container uk-container-center">
            <div class="section-header uk-text-center">
                <span class="sub-title">— Cảm nhận</span>
                <h2 class="section-title">CẢM NHẬN KHÁCH HÀNG</h2>
            </div>

            <div class="swiper-container about-testimonials-swiper">
                <div class="swiper-wrapper">
                    @foreach($feedbackPosts as $post)
                        @php
                            $lang = $post->languages->first();
                        @endphp
                        <div class="swiper-slide testimonial-slide-item">
                            <div class="testimonial-card">
                                <div class="quote-icon">
                                    <i class="fa fa-quote-left"></i>
                                </div>
                                <p class="quote-text">
                                    “{!! strip_tags($lang->description) !!}”
                                </p>
                                <div class="author-info uk-flex uk-flex-middle">
                                    <div class="author-avatar">
                                        <img src="{{ $post->image }}" alt="{{ $lang->name }}">
                                    </div>
                                    <div class="author-meta">
                                        <h4 class="author-name">{{ $lang->name }}</h4>
                                        <span class="author-role">{{ $post->short_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination testimonials-pagination"></div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section 5.5: Video giới thiệu -->
    @if(isset($system['homepage_about_video_url']) && !empty($system['homepage_about_video_url']))
    <div class="about-video-section">
        <div class="uk-container uk-container-center">
            <div class="section-header uk-text-center">
                @if(isset($system['homepage_about_video_title']) && !empty($system['homepage_about_video_title']))
                    <span class="sub-title">— Video</span>
                    <h2 class="section-title">{{ $system['homepage_about_video_title'] }}</h2>
                @endif
                @if(isset($system['homepage_about_video_desc']) && !empty($system['homepage_about_video_desc']))
                    <p class="section-desc" style="max-width: 700px; margin: 0 auto 30px auto; color: #6b7280; font-size: 14.5px; line-height: 1.65;">
                        {{ $system['homepage_about_video_desc'] }}
                    </p>
                @endif
            </div>
            <div class="video-player-wrapper">
                <div class="video-iframe-container">
                    {!! $system['homepage_about_video_url'] !!}
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Section 6: Hỗ trợ nhiệt tình -->
    <div class="about-support-section">
        <div class="uk-container uk-container-center">
            <div class="section-header uk-text-center">
                <span class="sub-title">— Dịch vụ</span>
                <h2 class="section-title">HỖ TRỢ NHIỆT TÌNH</h2>
            </div>

            <div class="uk-grid uk-grid-large uk-grid-width-medium-1-3 uk-grid-width-1-1 support-grid" data-uk-grid-margin>
                <!-- Column 1 -->
                <div class="support-item-wrapper">
                    <div class="support-item-card">
                        <div class="icon-circle">
                            <i class="fa fa-headphones"></i>
                        </div>
                        <h3 class="support-title">Nhận tư vấn</h3>
                        <p class="support-desc">
                            Đội ngũ chuyên viên giàu kinh nghiệm sẵn sàng lắng nghe và tư vấn giải pháp tối ưu cho không gian phòng tắm của bạn hoàn toàn miễn phí.
                        </p>
                    </div>
                </div>

                <!-- Column 2 -->
                <div class="support-item-wrapper">
                    <div class="support-item-card">
                        <div class="icon-circle">
                            <i class="fa fa-phone"></i>
                        </div>
                        <h3 class="support-title">Liên hệ</h3>
                        <p class="support-desc">
                            Kết nối nhanh chóng với Tazen qua Hotline 24/7, Zalo Chat hoặc Email để được xử lý nhanh nhất mọi yêu cầu đặt hàng và báo giá.
                        </p>
                    </div>
                </div>

                <!-- Column 3 -->
                <div class="support-item-wrapper">
                    <div class="support-item-card">
                        <div class="icon-circle">
                            <i class="fa fa-question-circle-o"></i>
                        </div>
                        <h3 class="support-title">Câu hỏi thường gặp</h3>
                        <p class="support-desc">
                            Giải đáp nhanh chóng các thắc mắc phổ biến về chính sách giao hàng, thanh toán, lắp đặt bồn tắm và bồi thường bảo hành.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>



</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Swiper for Projects (Matching homepage style)
        new Swiper('.project-swiper-about', {
            slidesPerView: 4,
            spaceBetween: 0,
            loop: true,
            autoplay: {
                delay: 4000,
                disableOnInteraction: false,
            },
            breakpoints: {
                320: { slidesPerView: 1.2, spaceBetween: 0 },
                640: { slidesPerView: 2, spaceBetween: 0 },
                960: { slidesPerView: 3, spaceBetween: 0 },
                1200: { slidesPerView: 4, spaceBetween: 0 }
            }
        });

        // Swiper for News (Matching mockup layout)
        new Swiper('.about-news-swiper', {
            slidesPerView: 2,
            spaceBetween: 25,
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.news-pagination',
                clickable: true,
            },
            breakpoints: {
                320: { slidesPerView: 1, spaceBetween: 15 },
                640: { slidesPerView: 2, spaceBetween: 15 },
                960: { slidesPerView: 2, spaceBetween: 25 }
            }
        });

        // Swiper for Testimonials
        new Swiper('.about-testimonials-swiper', {
            slidesPerView: 3,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.testimonials-pagination',
                clickable: true,
            },
            breakpoints: {
                320: { slidesPerView: 1, spaceBetween: 15 },
                768: { slidesPerView: 2, spaceBetween: 20 },
                1024: { slidesPerView: 3, spaceBetween: 30 }
            }
        });
    });
</script>
@endsection
