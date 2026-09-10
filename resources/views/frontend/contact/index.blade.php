@extends('frontend.homepage.layout')

@section('content')
    <!-- Khối Đầu Trang Chủ (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <section class="contact-page-wrapper">
        <div class="uk-container uk-container-center page-container">
            <!-- Breadcrumbs -->
            <div class="breadcrumbs-row uk-margin-bottom">
                <ul class="uk-list uk-clearfix uk-flex uk-flex-middle cat-hero-breadcrumbs">
                    <li><a href="/">Trang chủ</a></li>
                    <li class="separator"><i class="fa fa-angle-right"></i></li>
                    <li><a href="#" onclick="return false;" class="active-crumb">Liên hệ</a></li>
                </ul>
            </div>

            <!-- Page Title -->
            <div class="page-header-row uk-margin-large-bottom">
                <h1 class="page-title">LIÊN HỆ VỚI TRÚC GPS</h1>
            </div>

            {{-- Contact info cards row --}}
            <div class="contact-cards-row uk-grid uk-grid-large uk-grid-width-1-1 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3" data-uk-grid-margin>
                <div>
                    <div class="contact-card hover-animate">
                        <div class="card-icon">
                            <i class="fa fa-map-marker"></i>
                        </div>
                        <h3 class="card-title">Địa chỉ</h3>
                        <p class="card-value">{{ $system['contact_address'] ?? 'Số 1 Ngõ 5 Đường Động Lãm, Phường Phú Lương, TP Hà Nội' }}</p>
                    </div>
                </div>
                <div>
                    <div class="contact-card hover-animate">
                        <div class="card-icon">
                            <i class="fa fa-phone"></i>
                        </div>
                        <h3 class="card-title">Hotline / Zalo</h3>
                        <p class="card-value">
                            <a href="tel:{{ $system['contact_hotline'] ?? '0586139888' }}">{{ $system['contact_hotline'] ?? '0586.139.888' }}</a>
                        </p>
                    </div>
                </div>
                <div>
                    <div class="contact-card hover-animate">
                        <div class="card-icon">
                            <i class="fa fa-envelope"></i>
                        </div>
                        <h3 class="card-title">Email</h3>
                        <p class="card-value">
                            <a href="mailto:{{ $system['contact_email'] ?? 'info@trucgps.vn' }}">{{ $system['contact_email'] ?? 'info@trucgps.vn' }}</a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Main contact section: Form & Map --}}
            <div class="contact-main uk-grid uk-grid-large uk-margin-large-top" data-uk-grid-margin>
                {{-- Form Column (3/5) --}}
                <div class="uk-width-large-3-5 uk-width-medium-1-1">
                    <div class="contact-form-box">
                        <h2 class="form-title">GỬI YÊU CẦU CHO CHÚNG TÔI</h2>
                        <p class="form-desc">Vui lòng điền thông tin vào mẫu dưới đây, chúng tôi sẽ phản hồi trong vòng 24 giờ làm việc.</p>
                        
                        <form id="contact-form-submit" class="modal-form">
                            @csrf
                            <div class="uk-grid uk-grid-medium" data-uk-grid-margin>
                                <div class="uk-width-large-1-2 uk-width-medium-1-1">
                                    <div class="form-group">
                                        <label>Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" name="name" required placeholder="Nhập họ và tên..." class="form-control">
                                    </div>
                                </div>
                                <div class="uk-width-large-1-2 uk-width-medium-1-1">
                                    <div class="form-group">
                                        <label>Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="text" name="phone" required placeholder="Nhập số điện thoại..." class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" required placeholder="Nhập email liên hệ..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Chủ đề yêu cầu</label>
                                <input type="text" name="title" placeholder="Nhập tiêu đề hoặc chủ đề cần tư vấn..." class="form-control">
                            </div>
                            <div class="form-group">
                                <label>Nội dung chi tiết <span class="text-danger">*</span></label>
                                <textarea name="message" rows="5" required placeholder="Nhập nội dung tin nhắn hoặc câu hỏi của bạn ở đây..." class="form-control"></textarea>
                            </div>
                            <button type="submit" class="btn-submit-contact">Gửi lời nhắn liên hệ</button>
                        </form>
                    </div>
                </div>

                {{-- Map & Info Column (2/5) --}}
                <div class="uk-width-large-2-5 uk-width-medium-1-1">
                    <div class="contact-map-box">
                        @php
                            $mapUrl = $system['contact_map'] ?? '';
                            $embedMap = '';
                            if (!empty($mapUrl)) {
                                if (strpos($mapUrl, 'embed') !== false) {
                                    $embedMap = $mapUrl;
                                } else {
                                    $embedMap = 'https://maps.google.com/maps?q=' . urlencode($system['contact_address'] ?? 'Hà Nội') . '&output=embed';
                                }
                            } else {
                                $embedMap = 'https://maps.google.com/maps?q=' . urlencode($system['contact_address'] ?? 'H1 Khu Đấu Giá Phú Lương, Hà Nội') . '&output=embed';
                            }
                        @endphp
                        <div class="map-embed">
                            <iframe 
                                src="{{ $embedMap }}"
                                width="100%" 
                                height="280" 
                                style="border:0; border-radius: 6px;" 
                                allowfullscreen="" 
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>

                        <div class="working-hours">
                            <h3 class="hours-title"><i class="fa fa-clock-o"></i> GIỜ LÀM VIỆC</h3>
                            <ul class="hours-list uk-list">
                                <li>
                                    <span class="day">Thứ 2 – Thứ 6:</span>
                                    <span class="time">8:00 – 18:00</span>
                                </li>
                                <li>
                                    <span class="day">Thứ 7:</span>
                                    <span class="time">8:00 – 12:00</span>
                                </li>
                                <li>
                                    <span class="day">Chủ nhật:</span>
                                    <span class="time closed">Nghỉ</span>
                                </li>
                            </ul>
                        </div>

                        <div class="contact-social-row">
                            <a href="{{ $system['contact_facebook'] ?? '#' }}" target="_blank" class="social-contact-btn facebook">
                                <i class="fa fa-facebook"></i> Facebook Fanpage
                            </a>
                            <a href="tel:{{ $system['contact_hotline'] ?? '0586139888' }}" class="social-contact-btn phone">
                                <i class="fa fa-phone"></i> Hotline trực tuyến
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- AJAX contact form submit script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const contactForm = document.getElementById('contact-form-submit');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(contactForm);
                    
                    fetch('/ajax/contact/advise', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 10 || data.code === 200 || data.status === true) {
                            toastr.success('Cảm ơn bạn! Thông tin liên hệ đã được gửi thành công.');
                            contactForm.reset();
                        } else {
                            toastr.error('Gửi yêu cầu không thành công, vui lòng thử lại.');
                        }
                    })
                    .catch(error => {
                        toastr.error('Đường truyền lỗi, vui lòng thử lại.');
                    });
                });
            }
        });
    </script>

    <style>
        /* ===== CONTACT PAGE CUSTOM STYLES ===== */
        .contact-page-wrapper {
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

        /* Contact Cards */
        .contact-cards-row {
            margin-bottom: 30px;
        }
        .contact-card {
            background: #ffffff;
            border: 3px solid #000000;
            border-radius: 10px;
            padding: 25px 20px;
            text-align: center;
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            height: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        .contact-card .card-icon {
            width: 50px;
            height: 50px;
            background: #000000;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }
        .contact-card .card-icon i {
            color: #ffffff;
            font-size: 20px;
        }
        .contact-card .card-title {
            font-size: 16px;
            font-weight: 900;
            color: #000000;
            margin: 0 0 8px 0;
            text-transform: uppercase;
        }
        .contact-card .card-value {
            font-size: 13.5px;
            color: #444444;
            font-weight: 700;
            margin: 0;
            line-height: 1.5;
        }
        .contact-card .card-value a {
            color: #ff2a2a;
            text-decoration: none;
        }
        .contact-card .card-value a:hover {
            color: #000000;
        }

        /* Form Box */
        .contact-form-box {
            background: #ffffff;
            border: 3px solid #000000;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            box-sizing: border-box;
        }
        .form-title {
            font-size: 22px;
            font-weight: 950;
            color: #000000;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            line-height: 1.4;
        }
        .form-desc {
            font-size: 13.5px;
            color: #666666;
            font-weight: 700;
            margin-bottom: 25px;
        }
        .modal-form .form-group {
            margin-bottom: 15px;
        }
        .modal-form label {
            display: block;
            font-size: 13.5px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 5px;
        }
        .modal-form .form-control {
            width: 100%;
            box-sizing: border-box;
            border: 2px solid #000000 !important;
            border-radius: 6px !important;
            padding: 10px 15px !important;
            font-size: 14px !important;
            background: #ffffff !important;
            color: #000000 !important;
            font-weight: 600 !important;
            outline: none !important;
        }
        .modal-form .form-control:focus {
            border-color: #ff2a2a !important;
        }
        .btn-submit-contact {
            background: #000000;
            color: #ffffff;
            border: 3px solid #000000;
            border-radius: 6px;
            font-weight: 900;
            padding: 12px 30px;
            font-size: 15px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.25s ease;
            margin-top: 10px;
        }
        .btn-submit-contact:hover {
            background: #ffffff;
            color: #000000;
        }

        /* Map & Info Box */
        .contact-map-box {
            background: #ffffff;
            border: 3px solid #000000;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            box-sizing: border-box;
        }
        .map-embed {
            border: 2px solid #000000;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
        }
        .working-hours {
            border-top: 2px dashed #000000;
            padding-top: 15px;
            margin-bottom: 20px;
        }
        .hours-title {
            font-size: 15px;
            font-weight: 900;
            color: #000000;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .hours-list li {
            display: flex;
            justify-content: space-between;
            font-size: 13.5px;
            font-weight: 700;
            color: #444444;
            padding: 6px 0;
            border-bottom: 1px dashed #eaeaea;
        }
        .hours-list li .time {
            color: #000000;
            font-weight: 800;
        }
        .hours-list li .closed {
            color: #ff2a2a;
        }
        .contact-social-row {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .social-contact-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 0;
            border: 2px solid #000000;
            border-radius: 6px;
            font-weight: 900;
            font-size: 14px;
            text-transform: uppercase;
            text-decoration: none !important;
            transition: all 0.2s ease;
        }
        .social-contact-btn.facebook {
            background: #1877f2;
            color: #ffffff !important;
            border-color: #1877f2;
        }
        .social-contact-btn.facebook:hover {
            background: #ffffff;
            color: #1877f2 !important;
        }
        .social-contact-btn.phone {
            background: #ff2a2a;
            color: #ffffff !important;
            border-color: #ff2a2a;
        }
        .social-contact-btn.phone:hover {
            background: #ffffff;
            color: #ff2a2a !important;
        }

        /* Hover animations */
        .hover-animate:hover {
            transform: translateY(-5px);
            border-color: #ff2a2a;
            box-shadow: 0 10px 20px rgba(0,0,0,0.12);
        }

        /* Responsive styles */
        @media (max-width: 767px) {
            .page-title {
                font-size: 26px !important;
                line-height: 1.45 !important;
            }
            .form-title {
                font-size: 20px !important;
                line-height: 1.45 !important;
            }
            .hours-title {
                line-height: 1.45 !important;
            }
            .contact-cards-row > div {
                margin-bottom: 20px !important;
            }
            .contact-cards-row > div:last-child {
                margin-bottom: 0 !important;
            }
        }
    </style>
@endsection
