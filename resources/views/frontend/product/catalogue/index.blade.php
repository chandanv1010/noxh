@extends('frontend.homepage.layout')

@section('content')
    <!-- Khối Đầu Trang Chủ Tái Sử Dụng (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <div id="prd-catalogue" class="page-body">
        <div class="uk-container uk-container-center category-container">
            <div class="uk-grid uk-grid-large category-main-grid">
                
                <!-- CỘT TRÁI (1/4): Sản phẩm bán chạy & Tư vấn -->
                <div class="uk-width-large-1-4 uk-width-medium-1-3 uk-width-small-1-1 sidebar-left-col">
                    
                    <!-- Khối Sản Phẩm Bán Chạy -->
                    <div class="sidebar-widget best-sellers-widget">
                        <div class="widget-header">
                            <h3 class="widget-title">SẢN PHẨM BÁN CHẠY</h3>
                        </div>
                        <div class="widget-content">
                            @if(isset($widgets['best-seller-products']->object) && $widgets['best-seller-products']->object->isNotEmpty())
                                @foreach($widgets['best-seller-products']->object as $bestItem)
                                    @php
                                        $bestTitle = '';
                                        $bestCanonical = '#';
                                        if (isset($bestItem->languages)) {
                                            if ($bestItem->languages instanceof \Illuminate\Support\Collection) {
                                                $firstLang = $bestItem->languages->first();
                                                $bestTitle = $firstLang->pivot->name ?? $firstLang->name ?? '';
                                                $bestCanonical = $firstLang->pivot->canonical ?? $firstLang->canonical ?? '#';
                                            } else {
                                                $bestTitle = $bestItem->languages->pivot->name ?? $bestItem->languages->name ?? '';
                                                $bestCanonical = $bestItem->languages->pivot->canonical ?? $bestItem->languages->canonical ?? '#';
                                            }
                                        }
                                        $bestCanonical = write_url($bestCanonical);
                                        $bestImage = $bestItem->image;
                                        $bestOriginalPrice = $bestItem->price;
                                        $bestSalePrice = $bestItem->promotion_price ?? $bestItem->price;
                                        
                                        $bestParentCatName = '';
                                        if (isset($bestItem->product_catalogues) && count($bestItem->product_catalogues)) {
                                            $firstCat = $bestItem->product_catalogues->first();
                                            if (isset($firstCat->languages)) {
                                                if ($firstCat->languages instanceof \Illuminate\Support\Collection) {
                                                    $bestParentCatName = $firstCat->languages->first()->pivot->name ?? $firstCat->languages->first()->name ?? '';
                                                } else {
                                                    $bestParentCatName = $firstCat->languages->pivot->name ?? $firstCat->languages->name ?? '';
                                                }
                                            }
                                        }
                                    @endphp
                                    <div class="best-seller-item uk-flex uk-flex-top">
                                        <a href="{{ $bestCanonical }}" class="best-seller-img-link">
                                            <img src="{{ $bestImage }}" alt="{{ $bestTitle }}">
                                        </a>
                                        <div class="best-seller-info">
                                            <span class="best-seller-category">{{ $bestParentCatName }}</span>
                                            <h4 class="best-seller-name">
                                                <a href="{{ $bestCanonical }}" title="{{ $bestTitle }}">{{ $bestTitle }}</a>
                                            </h4>
                                            <div class="best-seller-price-row">
                                                @if($bestOriginalPrice > 0 && $bestOriginalPrice > $bestSalePrice)
                                                    <span class="old-price">{{ number_format($bestOriginalPrice, 0, ',', '.') }}đ</span>
                                                @endif
                                                <span class="new-price">{{ number_format($bestSalePrice, 0, ',', '.') }}đ</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <p class="no-widget-data">Không có dữ liệu</p>
                            @endif
                        </div>
                    </div>

                    <!-- Khối Tư Vấn Nhận Thông Tin -->
                    <div class="sidebar-widget consultation-widget">
                        <div class="widget-inner">
                            <h4 class="consult-heading">Bạn cần tư vấn sản phẩm phù hợp?</h4>
                            <p class="consult-subheading">Đội ngũ chuyên gia của TRUC sẵn sàng hỗ trợ bạn 24/7</p>
                            
                            <ul class="consult-bullets uk-list">
                                <li><i class="fa fa-check bullet-check"></i> Tư vấn giải pháp phù hợp</li>
                                <li><i class="fa fa-check bullet-check"></i> Khảo sát & báo giá nhanh</li>
                                <li><i class="fa fa-check bullet-check"></i> Hỗ trợ kỹ thuật tận tâm</li>
                            </ul>
                            
                            <!-- Trigger UIkit modal -->
                            <button type="button" class="btn-consult-submit" data-uk-modal="{target:'#modal-consultation'}">
                                NHẬN TƯ VẤN NGAY
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI (3/4): Tiêu đề, Mô tả, Lọc, Danh sách sản phẩm, Phân trang -->
                <div class="uk-width-large-3-4 uk-width-medium-2-3 uk-width-small-1-1 main-right-col">
                    
                    <!-- Thông tin danh mục (Tiêu đề + Mô tả) + Bộ lọc Sắp xếp -->
                    <div class="category-header-info-wrapper uk-flex uk-flex-top uk-flex-space-between">
                        <div class="category-header-left">
                            <div class="cat-title-row uk-flex uk-flex-middle">
                                @if(!empty($productCatalogue->icon))
                                    <img src="{{ asset($productCatalogue->icon) }}" alt="" class="cat-icon-img">
                                @else
                                    <i class="fa fa-desktop cat-icon"></i>
                                @endif
                                <h2 class="cat-page-title">{{ $productCatalogue->name }}</h2>
                            </div>
                            
                            @if(!empty($productCatalogue->description))
                                <div class="cat-description-box" id="cat-description-box">
                                    <div class="cat-desc-content" id="cat-desc-content">
                                        {!! $productCatalogue->description !!}
                                    </div>
                                    <div class="cat-desc-overlay" id="cat-desc-overlay"></div>
                                    <button type="button" class="btn-readmore-desc" id="btn-readmore-desc" onclick="toggleReadMoreDesc()">
                                        Xem thêm <i class="fa fa-angle-down"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        
                        <div class="category-header-right">
                            <div class="sort-right">
                                <select class="category-sort-select" id="sort-select" onchange="applySort(this.value)">
                                    @php
                                        $currentSort = request()->get('sort', 'newest');
                                    @endphp
                                    <option value="newest" {{ $currentSort == 'newest' ? 'selected' : '' }}>Sắp xếp: Mới nhất</option>
                                    <option value="oldest" {{ $currentSort == 'oldest' ? 'selected' : '' }}>Sắp xếp: Cũ nhất</option>
                                    <option value="price_desc" {{ $currentSort == 'price_desc' ? 'selected' : '' }}>Sắp xếp: Giá cao nhất</option>
                                    <option value="price_asc" {{ $currentSort == 'price_asc' ? 'selected' : '' }}>Sắp xếp: Giá thấp nhất</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm -->
                    <div class="category-products-grid">
                        @if(count($products))
                            <div class="uk-grid uk-grid-medium uk-grid-width-1-2 uk-grid-width-small-1-2 uk-grid-width-medium-1-3 uk-grid-width-large-1-3" data-uk-grid-margin>
                                @foreach($products as $product)
                                    @php
                                        $title = '';
                                        $canonical = '#';
                                        if (isset($product->languages)) {
                                            if ($product->languages instanceof \Illuminate\Support\Collection) {
                                                $firstLang = $product->languages->first();
                                                $title = $firstLang->pivot->name ?? $firstLang->name ?? '';
                                                $canonical = $firstLang->pivot->canonical ?? $firstLang->canonical ?? '#';
                                            } else {
                                                $title = $product->languages->pivot->name ?? $product->languages->name ?? '';
                                                $canonical = $product->languages->pivot->canonical ?? $product->languages->canonical ?? '#';
                                            }
                                        }
                                        $canonical = write_url($canonical);
                                        $image = $product->image;
                                        $originalPrice = $product->price;
                                        $salePrice = $product->promotion_price ?? $product->price;
                                        
                                        $parentCatName = '';
                                        if (isset($product->product_catalogues) && count($product->product_catalogues)) {
                                            $firstCat = $product->product_catalogues->first();
                                            if (isset($firstCat->languages)) {
                                                if ($firstCat->languages instanceof \Illuminate\Support\Collection) {
                                                    $parentCatName = $firstCat->languages->first()->pivot->name ?? $firstCat->languages->first()->name ?? '';
                                                } else {
                                                    $parentCatName = $firstCat->languages->pivot->name ?? $firstCat->languages->name ?? '';
                                                }
                                            }
                                        }
                                        $warranty = $product->warranty ?? '';
                                    @endphp
                                    <div class="uk-margin-bottom">
                                        <div class="product-card">
                                            <a href="{{ $canonical }}" class="product-card-image-link">
                                                <img src="{{ $image }}" alt="{{ $title }}" class="product-card-img">
                                            </a>
                                            
                                            <div class="product-card-info">
                                                <div class="product-card-category">{{ mb_strtoupper($parentCatName) }}</div>
                                                
                                                <h3 class="product-card-title">
                                                    <a href="{{ $canonical }}" title="{{ $title }}">{{ $title }}</a>
                                                </h3>
                                                
                                                <div class="product-card-warranty">
                                                    @if(!empty($warranty))
                                                        Bảo hành {{ $warranty }} tháng
                                                    @else
                                                        Bảo hành 12 tháng
                                                    @endif
                                                </div>
                                                
                                                <div class="product-card-price-row uk-flex uk-flex-middle">
                                                    @if($originalPrice > 0 && $originalPrice > $salePrice)
                                                        <span class="price-old">{{ number_format($originalPrice, 0, ',', '.') }}đ</span>
                                                    @endif
                                                    <span class="price-current">{{ number_format($salePrice, 0, ',', '.') }}đ</span>
                                                </div>
                                                
                                                <div class="product-card-actions uk-flex uk-flex-middle">
                                                    <a href="{{ $canonical }}" class="btn-buy-now">MUA NGAY</a>
                                                    <button type="button" class="btn-cart" onclick="addToCart({{ $product->id }})">
                                                        <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="" class="cart-btn-icon">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Phân trang (Center centered pagination) -->
                            <div class="category-pagination uk-flex uk-flex-center uk-margin-large-top">
                                @include('frontend.component.pagination', ['model' => $products])
                            </div>
                        @else
                            <div class="no-products-found uk-text-center uk-margin-large-top">
                                <i class="fa fa-info-circle info-icon"></i>
                                <p>Không tìm thấy sản phẩm nào trong danh mục này.</p>
                            </div>
                        @endif
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- Khối Banner Dịch vụ Trang Chủ (Banner Home) - full-width directly above footer -->
    @if(isset($slides['banner-home']['item']) && count($slides['banner-home']['item']))
        <section class="commit2-section banner-home-section">
            <div class="uk-container uk-container-center">
                <div class="commit2-grid">
                    @foreach($slides['banner-home']['item'] as $bannerHomeItem)
                        <div class="commit2-item">
                            <div class="commit2-card">
                                <div class="commit2-img-container">
                                    <img src="{{ asset($bannerHomeItem['image']) }}" alt="{{ $bannerHomeItem['name'] }}" class="commit2-img">
                                </div>
                                <div class="commit2-overlay">
                                    <h3 class="commit2-title">{{ $bannerHomeItem['name'] }}</h3>
                                    <p class="commit2-desc">{{ $bannerHomeItem['alt'] ?? '' }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- UIkit Modal Tư Vấn Nhận Thông Tin -->
    <div id="modal-consultation" class="uk-modal">
        <div class="uk-modal-dialog consult-modal-dialog">
            <a href="" class="uk-modal-close uk-close"></a>
            <div class="modal-header">
                <h3>ĐĂNG KÝ NHẬN TƯ VẤN MIỄN PHÍ</h3>
                <p>Vui lòng để lại thông tin liên hệ. Đội ngũ chuyên gia của chúng tôi sẽ gọi lại cho bạn trong vòng 15 phút.</p>
            </div>
            <form action="#" method="POST" class="consult-form" id="consultation-ajax-form">
                @csrf
                <input type="hidden" name="type" value="advise">
                <input type="hidden" name="title" value="Đăng ký tư vấn từ danh mục: {{ $productCatalogue->name }}">
                
                <div class="form-group uk-margin-bottom">
                    <label for="name">Họ và tên <span class="required">*</span></label>
                    <input type="text" name="name" id="name" required class="uk-width-1-1 modal-input" placeholder="Nhập họ và tên...">
                </div>
                
                <div class="form-group uk-margin-bottom">
                    <label for="phone">Số điện thoại <span class="required">*</span></label>
                    <input type="text" name="phone" id="phone" required class="uk-width-1-1 modal-input" placeholder="Nhập số điện thoại...">
                </div>

                <div class="form-group uk-margin-bottom">
                    <label for="email">Email (Không bắt buộc)</label>
                    <input type="email" name="email" id="email" class="uk-width-1-1 modal-input" placeholder="Nhập email...">
                </div>

                <div class="form-group uk-margin-bottom">
                    <label for="message">Nội dung cần tư vấn</label>
                    <textarea name="message" id="message" rows="3" class="uk-width-1-1 modal-textarea" placeholder="Ví dụ: Cần tư vấn camera hành trình Vietmap cho xe Honda City 2021..."></textarea>
                </div>

                <div class="form-actions uk-text-right">
                    <button type="button" class="uk-button uk-modal-close btn-modal-close">Đóng</button>
                    <button type="submit" class="uk-button btn-modal-submit">Gửi thông tin</button>
                </div>
            </form>
        </div>
    </div>


    <style>
        #prd-catalogue {
            background-color: #ffc600; /* Nền vàng đặc trưng toàn trang */
            padding: 40px 0 20px 0;
            font-family: var(--second-font), sans-serif;
            box-sizing: border-box;
        }

        .category-container {
            box-sizing: border-box;
        }

        /* 1/4 Sidebar Styles - Widget best sellers */
        .best-sellers-widget {
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            margin-bottom: 30px;
        }

        .best-sellers-widget .widget-header {
            background: transparent !important;
            color: #000000 !important;
            padding: 0 0 20px 0 !important;
            border-bottom: none !important;
        }

        .best-sellers-widget .widget-title {
            color: #000000 !important;
            font-size: 22px !important;
            font-weight: 900 !important;
            margin: 0 !important;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .best-sellers-widget .widget-content {
            padding: 0 !important;
        }

        .best-seller-item {
            padding: 15px 0 !important;
            border-bottom: none !important;
            gap: 15px !important;
            align-items: flex-start;
        }

        .best-seller-img-link {
            width: 100px !important;
            height: 100px !important;
            flex-shrink: 0;
            border: 2px solid #000000 !important;
            border-radius: 4px !important;
            overflow: hidden;
            background: #ffffff;
            display: block;
        }

        .best-seller-img-link img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .best-seller-info {
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            min-width: 0;
            padding-top: 2px;
        }

        .best-seller-category {
            font-size: 10px !important;
            color: #444444 !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            margin-bottom: 5px !important;
        }

        .best-seller-name {
            font-size: 13.5px !important;
            font-weight: 800 !important;
            line-height: 1.35 !important;
            margin: 0 0 6px 0 !important;
            height: auto !important;
            overflow: visible !important;
            display: block !important;
            -webkit-line-clamp: none !important;
        }

        .best-seller-name a {
            color: #000000 !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .best-seller-name a:hover {
            color: #ff5500 !important;
        }

        .best-seller-price-row {
            display: flex;
            flex-direction: column;
            align-items: flex-start !important;
            gap: 2px !important;
        }

        .best-seller-price-row .new-price {
            font-size: 14.5px !important;
            font-weight: 900 !important;
            color: #ff0000 !important;
        }

        .best-seller-price-row .old-price {
            font-size: 12px !important;
            color: #555555 !important;
            text-decoration: line-through !important;
            font-weight: 600 !important;
        }

        /* Consultation Widget */
        .consultation-widget {
            border: 2px solid #000000;
            background: #000000;
            color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            box-sizing: border-box;
        }

        .consultation-widget .widget-inner {
            padding: 25px 20px;
            text-align: left; /* Left align title & description */
        }

        .consult-heading {
            color: #ffc600;
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 10px 0;
            line-height: 1.35;
        }

        .consult-subheading {
            color: #cccccc;
            font-size: 12px;
            line-height: 1.4;
            margin: 0 0 20px 0;
        }

        .consult-bullets {
            text-align: left;
            margin: 0 0 25px 0;
        }

        .consult-bullets li {
            font-size: 12.5px;
            color: #ffffff;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bullet-check {
            color: #ffc600;
            font-size: 12px;
        }

        .btn-consult-submit {
            width: 100%;
            background: #ffc600;
            color: #000000;
            border: 2px solid #000000;
            border-radius: 6px;
            padding: 12px 0;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            transition: background 0.25s ease, color 0.25s ease;
            font-family: var(--second-font), sans-serif; /* Fix font error */
        }

        .btn-consult-submit:hover {
            background: #ffffff;
            color: #000000;
        }

        /* 3/4 Main Content Styles */
        .category-header-info-wrapper {
            margin-bottom: 30px;
            gap: 30px;
        }

        .category-header-left {
            flex: 1;
            min-width: 0;
        }

        .category-header-right {
            flex-shrink: 0;
            padding-top: 5px;
        }

        .cat-title-row {
            gap: 15px;
            margin-bottom: 12px;
            border-bottom: none;
            padding-bottom: 0;
        }

        .cat-title-row .cat-icon {
            font-size: 32px;
            color: #000000;
        }

        .cat-icon-img {
            width: 32px;
            height: 32px;
            object-fit: contain;
            filter: brightness(0);
        }

        .cat-page-title {
            font-size: 26px;
            font-weight: 900;
            color: #000000;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Description with readmore */
        .cat-description-box {
            position: relative;
            overflow: hidden;
            max-height: 110px; /* Hiển thị tối đa khoảng 5 dòng */
            transition: max-height 0.4s ease;
        }

        .cat-description-box.expanded {
            max-height: 2000px;
        }

        .cat-desc-content {
            font-size: 14.5px;
            line-height: 1.6;
            color: #000000;
            font-weight: 500;
        }

        .cat-desc-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 45px;
            background: linear-gradient(to bottom, rgba(255,198,0,0) 0%, rgba(255,198,0,1) 100%);
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .cat-description-box.expanded .cat-desc-overlay {
            opacity: 0;
        }

        .btn-readmore-desc {
            background: none;
            border: none;
            color: #000000;
            font-weight: 800;
            font-size: 13px;
            cursor: pointer;
            display: block;
            margin: 12px auto 0 auto;
            text-transform: uppercase;
            outline: none;
            text-decoration: underline;
        }

        .btn-readmore-desc:hover {
            color: #222222;
        }

        .category-sort-select {
            background: #ffc600 !important;
            border: 2px solid #000000 !important;
            border-radius: 6px !important;
            padding: 8px 30px 8px 15px !important;
            font-size: 13.5px !important;
            font-weight: 800 !important;
            color: #000000 !important;
            outline: none !important;
            cursor: pointer !important;
            font-family: var(--second-font), sans-serif !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='black' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: calc(100% - 12px) center !important;
        }

        /* Pagination Centered with Black Circles */
        .category-pagination {
            display: flex;
            justify-content: center;
            margin: 40px 0 0 0;
        }

        .category-pagination ul.pagination {
            display: flex;
            align-items: center;
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 10px;
        }

        .category-pagination ul.pagination li a,
        .category-pagination ul.pagination li span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: transparent;
            color: #000000;
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
            transition: all 0.25s ease;
            box-sizing: border-box;
            border: 1.5px solid transparent;
        }

        .category-pagination ul.pagination li.active span,
        .category-pagination ul.pagination li.uk-active span {
            background: #000000;
            color: #ffffff;
        }

        .category-pagination ul.pagination li a:hover {
            background: rgba(0,0,0,0.08);
            border-color: #000000;
        }

        /* Consultation Modal Dialog customization */
        .consult-modal-dialog {
            background: #ffffff !important;
            border: 3px solid #000000 !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-sizing: border-box;
        }

        .consult-modal-dialog .modal-header {
            margin-bottom: 20px;
            border-bottom: 2px solid #ffc600;
            padding-bottom: 15px;
        }

        .consult-modal-dialog .modal-header h3 {
            font-size: 18px;
            font-weight: 800;
            color: #000000;
            margin: 0 0 6px 0;
            text-transform: uppercase;
        }

        .consult-modal-dialog .modal-header p {
            font-size: 13px;
            color: #666666;
            margin: 0;
            line-height: 1.4;
        }

        .consult-form label {
            font-size: 13.5px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 6px;
            display: block;
        }

        .consult-form label .required {
            color: #d32f2f;
        }

        .modal-input {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #cccccc !important;
            border-radius: 4px !important;
            padding: 8px 12px !important;
            font-size: 13.5px !important;
            outline: none;
            box-sizing: border-box;
        }

        .modal-input:focus {
            border-color: #ffc600 !important;
        }

        .modal-textarea {
            background: #ffffff !important;
            color: #000000 !important;
            border: 1px solid #cccccc !important;
            border-radius: 4px !important;
            padding: 8px 12px !important;
            font-size: 13.5px !important;
            outline: none;
            box-sizing: border-box;
            resize: vertical;
        }

        .modal-textarea:focus {
            border-color: #ffc600 !important;
        }

        .form-actions {
            margin-top: 20px;
            gap: 12px;
            display: flex;
            justify-content: flex-end;
        }

        .btn-modal-close {
            background: #e0e0e0 !important;
            color: #333333 !important;
            font-weight: 700 !important;
            border: none !important;
            border-radius: 4px !important;
            padding: 8px 20px !important;
        }

        .btn-modal-submit {
            background: #000000 !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            border: none !important;
            border-radius: 4px !important;
            padding: 8px 25px !important;
            text-transform: uppercase !important;
            transition: background 0.25s ease !important;
        }

        .btn-modal-submit:hover {
            background: #ffc600 !important;
            color: #000000 !important;
        }

        .no-products-found {
            background: transparent !important;
            border: none !important;
            border-radius: 0 !important;
            padding: 40px 20px !important;
            color: #000000 !important;
            text-align: center !important;
        }

        .no-products-found .info-icon {
            font-size: 40px !important;
            color: #000000 !important;
            margin-bottom: 15px !important;
        }

        .no-products-found p {
            margin: 0 !important;
            font-weight: 800 !important;
            font-size: 16px !important;
        }

        /* Responsive Layout Grid fixes */
        @media (max-width: 959px) {
            .category-header-info-wrapper {
                flex-direction: column !important;
                gap: 15px !important;
            }
            .category-header-right {
                padding-top: 0;
                align-self: flex-start;
            }
        }

        @media (max-width: 767px) {
            .category-main-grid {
                flex-direction: column;
            }
            .sidebar-left-col {
                margin-bottom: 30px;
            }
        }
    </style>

    <!-- AJAX sorting & consultation form script -->
    <script>
        // Apply sorting type to the current URL
        function applySort(val) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', val);
            window.location.href = url.toString();
        }

        // Toggle Read More Description
        function toggleReadMoreDesc() {
            var box = document.getElementById('cat-description-box');
            var btn = document.getElementById('btn-readmore-desc');
            if (box.classList.contains('expanded')) {
                box.classList.remove('expanded');
                btn.innerHTML = 'Xem thêm <i class="fa fa-angle-down"></i>';
            } else {
                box.classList.add('expanded');
                btn.innerHTML = 'Thu gọn <i class="fa fa-angle-up"></i>';
            }
        }

        // Check if description needs "read more" on load
        document.addEventListener("DOMContentLoaded", function() {
            var box = document.getElementById('cat-description-box');
            var content = document.getElementById('cat-desc-content');
            var overlay = document.getElementById('cat-desc-overlay');
            var btn = document.getElementById('btn-readmore-desc');
            if (content && box && content.scrollHeight <= 110) {
                box.style.maxHeight = 'none';
                if (overlay) overlay.style.display = 'none';
                if (btn) btn.style.display = 'none';
            }
        });

        // AJAX Consultation Form submission
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('consultation-ajax-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const name = formData.get('name');
                    const phone = formData.get('phone');
                    const email = formData.get('email') || '';
                    const message = formData.get('message') || '';
                    const title = formData.get('title');

                    // Submit to advise route via AJAX
                    fetch('/ajax/contact/advise', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: name,
                            phone: phone,
                            email: email,
                            address: '',
                            message: `<div><h3>Yêu cầu tư vấn</h3><p><strong>Loại:</strong> ${title}</p><p><strong>Nội dung:</strong> ${message}</p></div>`
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.code === 10) {
                            alert('Gửi yêu cầu tư vấn thành công! Đội ngũ chuyên gia của chúng tôi sẽ sớm liên hệ với bạn.');
                            // Close UIkit modal
                            UIkit.modal("#modal-consultation").hide();
                            form.reset();
                        } else {
                            alert(data.messages && data.messages.name ? data.messages.name : 'Có lỗi xảy ra, vui lòng thử lại.');
                        }
                    })
                    .catch(error => {
                        console.error('Error submitting consultation form:', error);
                        alert('Có lỗi xảy ra kết nối hệ thống. Vui lòng thử lại.');
                    });
                });
            }
        });
    </script>
@endsection
