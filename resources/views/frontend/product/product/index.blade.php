@php
    // Chuẩn bị dữ liệu
    $prd_title = $product->languages->first()->pivot->name ?? $product->name;
    $prd_code = $product->code;
    $prd_model = $product->model ?? '';

    $albumSource = is_array($product->album) ? $product->album : json_decode($product->album ?? '[]', true);
    $list_image = array_values(array_filter(is_array($albumSource) ? $albumSource : []));

    if (!empty($product->image)) {
        array_unshift($list_image, $product->image);
    }

    $list_image = array_values(array_unique($list_image));
    $prd_href = write_url($product->languages->first()->pivot->canonical ?? '');
    $prd_description = $product->languages->first()->pivot->description ?? '';
    $prd_extend_des = $product->languages->first()->pivot->content ?? '';
    
    $price = number_format($product->price, 0, ',', '.');
    $oldPrice = number_format($product->combo_price, 0, ',', '.');
    $stockQuantity = (int) ($product->stock ?? 0);
    $wishlistItems = isset($wishlist) ? $wishlist : collect();
    $wishlistIds = $wishlistItems->pluck('id')->toArray();
    $isWishlisted = in_array($product->id, $wishlistIds);

    $catName = '';
    if (isset($productCatalogue->languages) && $productCatalogue->languages->isNotEmpty()) {
        $catName = $productCatalogue->languages->first()->pivot->name ?? $productCatalogue->name;
    }
@endphp

@extends('frontend.homepage.layout')

@section('content')
    <!-- Khối Đầu Trang Chủ / Trang danh mục (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <div id="prddetail" class="page-body">
        <div class="uk-container uk-container-center detail-container">
            <!-- Breadcrumbs -->
            <div class="detail-breadcrumbs-row">
                <ul class="uk-list uk-clearfix uk-flex uk-flex-middle cat-hero-breadcrumbs">
                    <li><a href="/">Trang chủ</a></li>
                    @if(!is_null($breadcrumb))
                        @foreach($breadcrumb as $key => $val)
                            @php
                                $bName = $val->languages->first()->pivot->name;
                                $bCanonical = write_url($val->languages->first()->pivot->canonical, true, true);
                            @endphp
                            <li class="separator"><i class="fa fa-angle-right"></i></li>
                            <li><a href="{{ $bCanonical }}">{{ $bName }}</a></li>
                        @endforeach
                    @endif
                    <li class="separator"><i class="fa fa-angle-right"></i></li>
                    <li><a href="#" onclick="return false;" class="active-crumb">{{ $prd_title }}</a></li>
                </ul>
            </div>

            <!-- Khối chi tiết sản phẩm chính (Chia 3/5 và 2/5) -->
            <div class="uk-grid uk-grid-large main-product-row" data-uk-grid-margin>
                <!-- CỘT TRÁI (3/5): Album ảnh vertical -->
                <div class="uk-width-large-3-5 uk-width-medium-1-1 uk-width-small-1-1">
                    <div class="product-gallery-wrapper">
                        <!-- Vertical Thumbnail List -->
                        <div class="gallery-thumbnails">
                            @foreach($list_image as $idx => $imgUrl)
                                <div class="thumbnail-item {{ $idx === 0 ? 'active' : '' }}" onclick="switchDetailImage('{{ asset($imgUrl) }}', this)">
                                    <img src="{{ asset($imgUrl) }}" alt="">
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Main Image Display -->
                        <div class="gallery-main-image">
                            <img id="main-detail-image" src="{{ asset($list_image[0] ?? '') }}" alt="{{ $prd_title }}">
                            @if($product->combo_price > 0 && $product->price > 0)
                                @php
                                    $discountPercent = round((($product->combo_price - $product->price) / $product->combo_price) * 100);
                                @endphp
                                @if($discountPercent > 0)
                                    <div class="gallery-promo-badge">
                                        <i class="fa fa-gift"></i> QUÀ TẶNG
                                    </div>
                                @endif
                            @endif
                            <div class="gallery-watermark">
                                <img src="/userfiles/image/icons/logo-watermark.png" alt="" onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI (2/5): Thông tin chi tiết + Ưu đãi + Nút bấm -->
                <div class="uk-width-large-2-5 uk-width-medium-1-1 uk-width-small-1-1 info-right-col">
                    <div class="product-meta">
                        <div class="prd-cat-tag">{{ mb_strtoupper($catName) }}</div>
                        <h1 class="prd-name">{{ $prd_title }}</h1>
                        
                        <!-- Ẩn sao đánh giá theo yêu cầu -->
                        <!--
                        <div class="prd-rating-row uk-flex uk-flex-middle">
                            <div class="stars uk-flex uk-flex-middle">
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                                <i class="fa fa-star"></i>
                            </div>
                            <span class="rating-value">4.9 (30 đánh giá)</span>
                        </div>
                        -->

                        <!-- Giá sản phẩm -->
                        <div class="prd-price-row uk-flex uk-flex-middle">
                            <span class="price-current">{{ $price }}đ</span>
                            @if($product->combo_price > 0 && $product->combo_price > $product->price)
                                <span class="price-old">{{ $oldPrice }}đ</span>
                            @endif
                        </div>

                        <!-- Khối ưu đãi -->
                        @php
                            // Ưu tiên nội dung khuyến mãi riêng của sản phẩm, nếu không có thì dùng
                            // ưu đãi chung khai báo tại Cấu hình hệ thống > Thông tin chung.
                            $offerContent = !empty($product->promotion_content)
                                ? $product->promotion_content
                                : ($system['homepage_shared_offer'] ?? '');
                            $offerTitle = $system['homepage_shared_offer_title'] ?? '';
                        @endphp
                        @if($product->no_offer != 1 && trim(strip_tags($offerContent, '<img>')) !== '')
                            <div class="offers-box">
                                @if($offerTitle !== '')
                                    <div class="offers-header">
                                        <span>{{ $offerTitle }}</span>
                                    </div>
                                @endif
                                <div class="offers-content">
                                    {!! $offerContent !!}
                                </div>
                            </div>
                        @endif

                        <!-- Nhóm nút bấm hành động -->
                        <div class="detail-actions">
                            <div class="actions-row-primary">
                                <a href="#buy-now-modal" data-uk-modal class="btn-detail-buy">MUA NGAY</a>
                                <button type="button" class="btn-detail-add-cart addToCart" data-id="{{ $product->id }}">
                                    <i class="fa fa-shopping-cart"></i> THÊM GIỎ HÀNG
                                </button>
                            </div>
                            
                            <div class="actions-row-secondary">
                                <a href="tel:{{ $system['contact_hotline'] ?? '0586.139.888' }}" class="btn-detail-call">
                                    <i class="fa fa-phone"></i> GỌI NGAY
                                </a>
                                @php
                                    $zaloPhone = preg_replace('/[^0-9]/', '', $system['contact_hotline'] ?? '0586139888');
                                @endphp
                                <a href="https://zalo.me/{{ $zaloPhone }}" target="_blank" class="btn-detail-zalo">
                                    <img src="/userfiles/image/icons/zalo-icon.png" alt="" style="width: 20px; height: 20px;" onerror="this.src='https://chat.zalo.me/favicon.ico'"> CHAT ZALO
                                </a>
                            </div>
                        </div>

                        <!-- Chia sẻ và hotline đại lý -->
                        <div class="share-and-contact uk-flex uk-flex-middle uk-flex-space-between uk-margin-top">
                            <div class="share-row uk-flex uk-flex-middle">
                                <span class="share-label">Chia sẻ:</span>
                                <div class="share-icons uk-flex uk-flex-middle">
                                    <a href="#" onclick="navigator.clipboard.writeText(window.location.href); toastr.success('Đã sao chép liên kết vào bộ nhớ tạm!'); return false;" class="share-btn link-btn" title="Copy Link"><i class="fa fa-link"></i></a>
                                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" class="share-btn fb-btn" title="Facebook"><i class="fa fa-facebook"></i></a>
                                </div>
                            </div>
                            <div class="agency-call">
                                👉 Gọi: <strong>0987622266 | 0845622266 | 0399622266</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Khối Switcher Tabs (Mô tả & Thông số) -->
            <div class="detail-tabs-section uk-margin-large-top">
                <ul class="uk-tab switcher-tabs" data-uk-tab="{connect:'#switcher-content'}">
                    <li class="uk-active"><a href="#" onclick="return false;">Thông tin chi tiết</a></li>
                    <li><a href="#" onclick="return false;">Thông số kỹ thuật</a></li>
                </ul>
                
                <ul id="switcher-content" class="uk-switcher switcher-panes uk-margin-top">
                    <!-- Thông tin chi tiết -->
                    <li class="pane-content editor-style">
                        @if(!empty($prd_description))
                            {!! $prd_description !!}
                        @else
                            <p class="no-info">Đang cập nhật thông tin chi tiết cho sản phẩm...</p>
                        @endif
                    </li>
                    <!-- Thông số kỹ thuật -->
                    <li class="pane-content editor-style">
                        @if(!empty($prd_extend_des))
                            {!! $prd_extend_des !!}
                        @else
                            <p class="no-info">Đang cập nhật thông số kỹ thuật cho sản phẩm...</p>
                        @endif
                    </li>
                </ul>
            </div>

            <!-- Thông tin đơn vị cuối trang chi tiết -->
            <div class="company-footer-info">
                @if(!empty($system['contact_signature']))
                    {!! $system['contact_signature'] !!}
                @elseif(!empty($system['homepage_company_info']))
                    {!! $system['homepage_company_info'] !!}
                @else
                    <h3>CÔNG TY TNHH TRUC COM VN</h3>
                    <ul class="uk-list info-list">
                        <li>🏠 <strong>CÔNG TY TNHH TRUC COM VN</strong></li>
                        <li>🏠 H1 Khu Đấu Giá Phú Lương, Phú Lương, Hà Nội</li>
                        <li>☎️ <strong>0854622266 | 0343622266</strong> Hotline</li>
                        <li>☎️ <strong>0845622266 | 0987622266</strong> Đại Lý-Hãng Xe</li>
                        <li>☎️ <strong>0765622266 | 0764622266</strong> Kĩ Thuật</li>
                        <li>☎️ <strong>0852622266 | 0813622266</strong> Gia Hạn</li>
                        <li>☎️ <strong>0399622266 | 0377622266</strong> Khiếu Nại Phản Ánh</li>
                    </ul>
                @endif
            </div>

            <!-- Sản phẩm liên quan -->
            @if(isset($productRelated) && $productRelated->isNotEmpty())
                <section class="related-products-section uk-margin-large-top">
                    <h2 class="related-title">SẢN PHẨM LIÊN QUAN</h2>
                    <div class="product-grid uk-grid uk-grid-medium uk-grid-width-1-2 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-4" data-uk-grid-margin>
                        @foreach($productRelated as $relPrd)
                            @php
                                $relName = '';
                                if (isset($relPrd->languages) && $relPrd->languages->isNotEmpty()) {
                                    $firstLang = $relPrd->languages->first();
                                    $relName = $firstLang->pivot->name ?? $firstLang->name ?? '';
                                }
                                $relCanonical = '#';
                                if (isset($relPrd->languages) && $relPrd->languages->isNotEmpty()) {
                                    $firstLang = $relPrd->languages->first();
                                    $relCanonical = write_url($firstLang->pivot->canonical ?? $firstLang->canonical ?? '#');
                                }
                                $relCatName = '';
                                if (isset($relPrd->product_catalogues) && $relPrd->product_catalogues->isNotEmpty()) {
                                    $firstCat = $relPrd->product_catalogues->first();
                                    if (isset($firstCat->languages) && $firstCat->languages->isNotEmpty()) {
                                        $relCatName = $firstCat->languages->first()->pivot->name ?? $firstCat->languages->first()->name ?? '';
                                    }
                                }
                                $relImage = asset($relPrd->image);
                                $relPrice = number_format($relPrd->price, 0, ',', '.');
                                $relOldPrice = number_format($relPrd->combo_price, 0, ',', '.');
                                $relWarrantyText = isset($relPrd->warranty) && $relPrd->warranty > 0 ? 'Bảo hành ' . $relPrd->warranty . ' tháng' : 'Bảo hành 12 tháng';
                            @endphp
                            <div class="product-card-wrapper">
                                <div class="product-card">
                                    <a href="{{ $relCanonical }}" class="product-card-image-link">
                                        <img src="{{ $relImage }}" alt="{{ $relName }}" class="product-card-img">
                                    </a>
                                    <div class="product-card-info">
                                        <div class="product-card-category">{{ mb_strtoupper($relCatName) }}</div>
                                        <h3 class="product-card-title">
                                            <a href="{{ $relCanonical }}" title="{{ $relName }}">{{ $relName }}</a>
                                        </h3>
                                        <div class="product-card-warranty">{{ $relWarrantyText }}</div>
                                        <div class="product-card-price-row uk-flex uk-flex-middle">
                                            @if($relPrd->combo_price > 0 && $relPrd->combo_price > $relPrd->price)
                                                <span class="price-old">{{ $relOldPrice }}đ</span>
                                            @endif
                                            <span class="price-current">{{ $relPrice }}đ</span>
                                        </div>
                                        <div class="product-card-actions uk-flex uk-flex-middle">
                                            <a href="{{ $relCanonical }}" class="btn-buy-now">MUA NGAY</a>
                                            <a href="{{ $relCanonical }}" class="btn-cart">
                                                <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="" class="cart-btn-icon">
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>

    <!-- Buy Now Modal Popup -->
    <div id="buy-now-modal" class="uk-modal custom-buy-modal">
        <div class="uk-modal-dialog uk-modal-dialog-large">
            <button type="button" class="uk-modal-close uk-close"></button>
            <div class="modal-header">
                <h3>ĐẶT MUA SẢN PHẨM</h3>
                <p class="modal-subtitle">Để lại thông tin dưới đây để nhận khuyến mãi và tư vấn lắp đặt miễn phí</p>
            </div>
            <form id="buy-now-form" class="modal-form">
                @csrf
                <div class="form-group">
                    <label>Sản phẩm đã chọn</label>
                    <input type="text" name="order_title_prd" value="{{ $prd_title }}" class="form-control text-readonly" readonly>
                </div>
                <div class="uk-grid uk-grid-medium" data-uk-grid-margin>
                    <div class="uk-width-large-1-2 uk-width-medium-1-1">
                        <div class="form-group">
                            <label>Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="order_name" required placeholder="Nhập họ và tên..." class="form-control">
                        </div>
                    </div>
                    <div class="uk-width-large-1-2 uk-width-medium-1-1">
                        <div class="form-group">
                            <label>Số điện thoại <span class="text-danger">*</span></label>
                            <input type="text" name="order_phone" required placeholder="Nhập số điện thoại..." class="form-control">
                        </div>
                    </div>
                </div>
                <div class="uk-grid uk-grid-medium" data-uk-grid-margin>
                    <div class="uk-width-large-1-2 uk-width-medium-1-1">
                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="order_email" required placeholder="Nhập email..." class="form-control">
                        </div>
                    </div>
                    <div class="uk-width-large-1-2 uk-width-medium-1-1">
                        <div class="form-group">
                            <label>Địa chỉ giao hàng <span class="text-danger">*</span></label>
                            <input type="text" name="order_address" required placeholder="Nhập địa chỉ giao hàng..." class="form-control">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ghi chú (dòng xe, năm sản xuất, yêu cầu khác...)</label>
                    <textarea name="order_message" rows="3" placeholder="Nhập ghi chú thêm nếu có..." class="form-control"></textarea>
                </div>
                <div class="modal-actions uk-text-right">
                    <button type="button" class="uk-button uk-modal-close btn-modal-close">Hủy</button>
                    <button type="submit" class="uk-button btn-modal-submit">Gửi đơn hàng</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Switch Image script and Form submit script -->
    <script>
        function switchDetailImage(src, element) {
            document.getElementById('main-detail-image').src = src;
            // Clear active on all thumbs
            const thumbs = document.querySelectorAll('.thumbnail-item');
            thumbs.forEach(t => t.classList.remove('active'));
            // Set active on clicked thumb
            element.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const buyForm = document.getElementById('buy-now-form');
            if (buyForm) {
                buyForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(buyForm);
                    
                    fetch('/ajax/order/buy/now', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 200) {
                            toastr.success('Đặt mua sản phẩm thành công! Chúng tôi sẽ liên hệ lại quý khách sớm nhất.');
                            buyForm.reset();
                            // Close modal
                            UIkit.modal('#buy-now-modal').hide();
                        } else {
                            toastr.error(data.message ? data.message : 'Có lỗi xảy ra, vui lòng kiểm tra lại thông tin.');
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
        /* ===== PRODUCT DETAIL PAGE CUSTOM STYLE ===== */
        #prddetail {
            background-color: #ffc600; /* Nền vàng đặc trưng đồng bộ */
            padding: 40px 0 60px 0;
            font-family: var(--second-font), sans-serif;
            box-sizing: border-box;
        }

        .detail-container {
            box-sizing: border-box;
        }

        /* Breadcrumbs */
        .detail-breadcrumbs-row {
            margin-bottom: 25px;
        }
        .cat-hero-breadcrumbs {
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 8px;
        }
        .cat-hero-breadcrumbs li, .cat-hero-breadcrumbs li a {
            font-size: 13.5px;
            color: #000000;
            font-weight: normal;
            text-decoration: none;
        }
        .cat-hero-breadcrumbs li a:hover {
            color: #ff2a2a;
        }
        .cat-hero-breadcrumbs li.separator {
            color: #000000;
            font-weight: normal;
        }
        .cat-hero-breadcrumbs li .active-crumb {
            color: #555555;
            cursor: default;
            pointer-events: none;
        }

        /* 3/5 - 2/5 Layout */
        .main-product-row {
            margin-bottom: 40px;
        }

        /* Vertical Gallery styles */
        .product-gallery-wrapper {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .gallery-thumbnails {
            width: 90px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            flex-shrink: 0;
        }
        .thumbnail-item {
            width: 90px;
            height: 90px;
            border: 3px solid #000000;
            border-radius: 2px;
            overflow: hidden;
            cursor: pointer;
            background: #ffffff;
            transition: all 0.2s ease;
            opacity: 0.6;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2px;
            box-sizing: border-box;
        }
        .thumbnail-item img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .thumbnail-item:hover, .thumbnail-item.active {
            opacity: 1;
            border-color: #ff2a2a;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        .gallery-main-image {
            flex: 1;
            border: 6px solid #000000;
            border-radius: 2px;
            background: #ffffff;
            overflow: hidden;
            position: relative;
            aspect-ratio: 1 / 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            box-sizing: border-box;
        }
        .gallery-main-image img#main-detail-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }
        .gallery-main-image:hover img#main-detail-image {
            transform: scale(1.03);
        }
        .gallery-promo-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #ff2a2a;
            color: #ffffff;
            font-size: 11px;
            font-weight: 900;
            padding: 6px 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .gallery-watermark {
            position: absolute;
            top: 15px;
            right: 15px;
            opacity: 0.85;
            max-width: 100px;
        }
        .gallery-watermark img {
            max-width: 100%;
            object-fit: contain;
        }

        /* Right Column Info */
        .info-right-col {
            box-sizing: border-box;
        }
        .prd-cat-tag {
            font-family: 'Montserrat', sans-serif;
            font-size: 13px;
            font-weight: normal;
            color: #555555;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .prd-name {
            font-family: 'Montserrat', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: #000000;
            margin: 0 0 10px 0;
            line-height: 1.2;
            text-transform: uppercase;
        }
        .prd-rating-row {
            gap: 8px;
            margin-bottom: 20px;
        }
        .prd-rating-row .stars i {
            color: #000000;
            font-size: 15px;
        }
        .prd-rating-row .rating-value {
            font-size: 13.5px;
            font-weight: 700;
            color: #000000;
        }
        .prd-price-row {
            gap: 15px;
            margin-bottom: 20px;
        }
        .prd-price-row .price-current {
            font-size: 36px;
            font-weight: 950;
            color: #ff2a2a;
        }
        .prd-price-row .price-old {
            font-size: 18px;
            font-weight: 700;
            color: #555555;
            text-decoration: line-through;
        }

        /* Offers Box */
        .offers-box {
            border: 2px solid #000000;
            border-radius: 0;
            margin: 25px 0;
            position: relative;
            background: transparent;
            padding: 30px 18px 18px 18px;
        }
        .offers-header {
            position: absolute;
            top: -15px;
            left: -12px;
            background: #ff2a2a;
            color: #ffffff;
            font-weight: 900;
            font-size: 13px;
            padding: 6px 16px;
            letter-spacing: 0.5px;
            box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.15);
            text-transform: uppercase;
        }
        .offers-header::before {
            content: '';
            position: absolute;
            top: 100%;
            left: 0;
            border-top: 6px solid #b30000;
            border-left: 6px solid transparent;
            width: 0;
            height: 0;
        }
        .offers-content {
            margin-top: 10px;
        }
        .offers-content ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
            counter-reset: li-counter;
        }
        .offers-content ul li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 14px;
            font-weight: normal;
            color: #000000;
            line-height: 1.5;
        }
        .offers-content ul li::before {
            content: counter(li-counter);
            counter-increment: li-counter;
            background: #000000;
            color: #ffffff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
            margin-top: 1px;
        }

        /* Action Buttons */
        .detail-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 25px;
        }
        .actions-row-primary {
            display: flex;
            gap: 12px;
        }
        .btn-detail-buy {
            flex: 1;
            background: #000000;
            color: #ffffff !important;
            text-transform: uppercase;
            font-weight: 950;
            font-size: 16px;
            padding: 14px 0;
            border: 3px solid #000000;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            letter-spacing: 0.5px;
            text-decoration: none !important;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-detail-buy:hover {
            background: #ffffff;
            color: #000000 !important;
        }
        .btn-detail-add-cart {
            flex: 1;
            font-family: inherit;
            background: #ffc600;
            color: #000000 !important;
            text-transform: uppercase;
            font-weight: 950;
            font-size: 16px;
            padding: 14px 0;
            border: 3px solid #000000;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            outline: none;
        }
        .btn-detail-add-cart:hover {
            background: #000000;
            color: #ffffff !important;
        }
        .actions-row-secondary {
            display: flex;
            gap: 12px;
        }
        .btn-detail-call {
            flex: 1;
            background: #ff2a2a;
            color: #ffffff !important;
            text-transform: uppercase;
            font-weight: 950;
            font-size: 15px;
            padding: 12px 0;
            border: 3px solid #ff2a2a;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none !important;
        }
        .btn-detail-call:hover {
            background: #ffffff;
            color: #ff2a2a !important;
        }
        .btn-detail-zalo {
            flex: 1;
            background: #2196f3;
            color: #ffffff !important;
            text-transform: uppercase;
            font-weight: 950;
            font-size: 15px;
            padding: 12px 0;
            border: 3px solid #2196f3;
            border-radius: 2px;
            cursor: pointer;
            transition: all 0.25s ease;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none !important;
        }
        .btn-detail-zalo:hover {
            background: #ffffff;
            color: #2196f3 !important;
        }

        /* Share & Agency Call */
        .share-and-contact {
            border-top: 1px solid rgba(0,0,0,0.1);
            padding-top: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .share-label {
            font-size: 13.5px;
            font-weight: 800;
            color: #000000;
            margin-right: 8px;
        }
        .share-icons {
            gap: 8px;
        }
        .share-btn {
            width: 32px;
            height: 32px;
            border: 2px solid #000000;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #000000 !important;
            background: #ffffff;
            transition: all 0.25s ease;
        }
        .share-btn:hover {
            background: #000000;
            color: #ffffff !important;
        }
        .agency-call {
            font-size: 13.5px;
            color: #000000;
            font-weight: 700;
        }
        .agency-call strong {
            font-weight: 900;
            color: #ff2a2a;
        }

        /* Switcher Tabs Styles */
        .switcher-tabs {
            border-bottom: 2px solid #000000 !important;
            padding: 0;
            margin: 0;
        }
        .switcher-tabs li {
            margin-bottom: -2px;
        }
        .switcher-tabs li a {
            border: none !important;
            background: transparent !important;
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 16px !important;
            padding: 10px 20px !important;
            text-transform: uppercase !important;
            border-radius: 0 !important;
            position: relative;
            text-decoration: none !important;
            transition: all 0.25s ease;
        }
        .switcher-tabs li.uk-active a {
            background: transparent !important;
            color: #000000 !important;
            font-weight: 900 !important;
        }
        .switcher-tabs li.uk-active a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #000000;
        }
        .pane-content {
            background: transparent !important;
            border: none !important;
            padding: 30px 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        .pane-content.editor-style {
            font-size: 15px;
            line-height: 1.8;
            color: #222222;
        }
        .pane-content.editor-style h2, .pane-content.editor-style h3 {
            font-weight: 900;
            color: #000000;
            margin-top: 25px;
        }
        .pane-content.editor-style img {
            max-width: 100%;
            height: auto;
            border-radius: 2px;
            margin: 15px 0;
            border: 2px solid #000000;
        }
        .no-info {
            text-align: center;
            font-style: italic;
            color: #666666;
            margin: 20px 0;
        }

        /* Company footer info */
        .company-footer-info {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px dashed #000000;
        }
        .company-footer-info h3 {
            font-size: 18px;
            font-weight: 900;
            color: #000000;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .company-footer-info ul.info-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .company-footer-info ul.info-list li {
            font-size: 14px;
            color: #333333;
            font-weight: 700;
            line-height: 1.5;
        }
        .company-footer-info ul.info-list li i {
            width: 20px;
            color: #000000;
        }

        /* Related products section */
        .related-products-section {
            border-top: 2px dashed #000000;
            padding-top: 40px;
            margin-top: 50px;
        }
        .related-products-section .product-card-wrapper {
            margin-bottom: 30px !important;
        }
        .related-title {
            font-size: 24px;
            font-weight: 950;
            color: #000000;
            text-transform: uppercase;
            margin-bottom: 25px;
            letter-spacing: 0.5px;
        }

        /* Buy Now Modal Custom Styles */
        .custom-buy-modal .uk-modal-dialog {
            background: #ffc600 !important;
            border: 4px solid #000000 !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3) !important;
        }
        .custom-buy-modal .uk-close {
            color: #000000 !important;
            opacity: 0.8;
            font-size: 24px;
        }
        .custom-buy-modal .uk-close:hover {
            opacity: 1;
        }
        .modal-header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #000000;
            padding-bottom: 15px;
        }
        .modal-header h3 {
            font-size: 22px;
            font-weight: 950;
            color: #000000;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        .modal-subtitle {
            font-size: 13.5px;
            color: #000000;
            font-weight: 700;
            margin: 0;
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
        .modal-form .text-readonly {
            background: #eaeaea !important;
            cursor: not-allowed;
            color: #555555 !important;
        }
        .modal-actions {
            margin-top: 25px;
            border-top: 2px solid #000000;
            padding-top: 15px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .btn-modal-close {
            background: #ffffff !important;
            color: #000000 !important;
            border: 2px solid #000000 !important;
            border-radius: 6px !important;
            font-weight: 800 !important;
            padding: 8px 20px !important;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-modal-close:hover {
            background: #eaeaea !important;
        }
        .btn-modal-submit {
            background: #000000 !important;
            color: #ffffff !important;
            border: 2px solid #000000 !important;
            border-radius: 6px !important;
            font-weight: 800 !important;
            padding: 8px 20px !important;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-modal-submit:hover {
            background: #ffffff !important;
            color: #000000 !important;
        }

        /* Responsive styles */
        @media (max-width: 959px) {
            .product-gallery-wrapper {
                flex-direction: column-reverse;
            }
            .gallery-thumbnails {
                width: 100%;
                flex-direction: row;
                overflow-x: auto;
                padding-bottom: 8px;
            }
            .thumbnail-item {
                width: 80px;
                height: 80px;
                flex-shrink: 0;
            }
            .prd-name {
                font-size: 26px;
                margin-top: 20px;
            }
            .prd-price-row .price-current {
                font-size: 30px;
            }
        }
    </style>
@endsection
