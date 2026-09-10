@extends('frontend.homepage.layout')
@section('content')
    <!-- Khối Đầu Trang Chủ (Menu dọc + Slide + Cam kết) -->
    @include('frontend.component.hero_section')

    <!-- Khối Cam Kết Chất Lượng 2 (Commit 2 Banners) -->
    @if(isset($slides['commit-2']['item']) && count($slides['commit-2']['item']))
        <section class="commit2-section">
            <div class="uk-container uk-container-center">
                <div class="commit2-wrapper">
                    <div class="commit2-grid">
                        @foreach($slides['commit-2']['item'] as $commit2Item)
                            <div class="commit2-item">
                                <div class="commit2-content uk-flex uk-flex-middle">
                                    <img src="{{ asset($commit2Item['image']) }}" alt="{{ $commit2Item['name'] }}" class="commit2-icon">
                                    <div class="commit2-text">
                                        <div class="commit2-title">{{ $commit2Item['name'] }}</div>
                                        <div class="commit2-desc">{{ $commit2Item['alt'] ?? '' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Khối Danh Mục Sản Phẩm Trang Chủ (4 Khối Demo, 8 Sản phẩm mỗi khối) -->
    @if(isset($widgets['homepage-categories']->object) && $widgets['homepage-categories']->object->isNotEmpty())
        @foreach($widgets['homepage-categories']->object as $cat)
            @if(isset($cat->products) && $cat->products->isNotEmpty())
                @php
                    $catName = '';
                    $catCanonical = '#';
                    if (isset($cat->languages) && $cat->languages->isNotEmpty()) {
                        $firstLang = $cat->languages->first();
                        $catName = $firstLang->pivot->name ?? $firstLang->name ?? '';
                        $catCanonical = write_url($firstLang->pivot->canonical ?? $firstLang->canonical ?? '#');
                    }
                @endphp
                <section class="category-product-section">
                    <div class="uk-container uk-container-center">
                        <div class="category-block-header uk-flex uk-flex-middle uk-flex-space-between">
                            <div class="header-left uk-flex uk-flex-middle">
                                @if(!empty($cat->icon))
                                    <img src="{{ asset($cat->icon) }}" alt="" class="category-header-icon">
                                @endif
                                <h2 class="category-header-title">
                                    {{ $catName }}
                                </h2>
                            </div>
                            <a href="{{ $catCanonical }}" class="view-all-link">
                                Xem tất cả <i class="fa fa-angle-right"></i>
                            </a>
                        </div>
                        
                        <div class="product-grid">
                            @foreach($cat->products->take(8) as $product)
                                @php
                                    $pName = '';
                                    if (isset($product->languages) && $product->languages->isNotEmpty()) {
                                        $firstLang = $product->languages->first();
                                        if (isset($firstLang->name)) {
                                            $pName = $firstLang->name;
                                        } elseif (isset($firstLang->pivot) && isset($firstLang->pivot->name)) {
                                            $pName = $firstLang->pivot->name;
                                        }
                                    }
                                    
                                    $pCanonical = '#';
                                    if (isset($product->languages) && $product->languages->isNotEmpty()) {
                                        $firstLang = $product->languages->first();
                                        if (isset($firstLang->canonical)) {
                                            $pCanonical = write_url($firstLang->canonical);
                                        } elseif (isset($firstLang->pivot) && isset($firstLang->pivot->canonical)) {
                                            $pCanonical = write_url($firstLang->pivot->canonical);
                                        }
                                    }
                                    
                                    $pImage = asset($product->image);
                                    $price = number_format($product->price, 0, ',', '.');
                                    $oldPrice = number_format($product->combo_price, 0, ',', '.');
                                    
                                    $warrantyText = '';
                                    if (isset($product->warranty) && $product->warranty > 0) {
                                        $warrantyText = 'Bảo hành ' . $product->warranty . ' tháng';
                                    } else {
                                        $warrantyText = 'Bảo hành 12 tháng';
                                    }
                                @endphp
                                <div class="product-card-wrapper">
                                    <div class="product-card">
                                        <a href="{{ $pCanonical }}" class="product-card-image-link">
                                            <img src="{{ $pImage }}" alt="{{ $pName }}" class="product-card-img">
                                        </a>
                                        
                                        <div class="product-card-info">
                                            <div class="product-card-category">
                                                {{ mb_strtoupper($catName) }}
                                            </div>
                                            
                                            <h3 class="product-card-title">
                                                <a href="{{ $pCanonical }}" title="{{ $pName }}">{{ $pName }}</a>
                                            </h3>
                                            
                                            <div class="product-card-warranty">{{ $warrantyText }}</div>
                                            
                                            <div class="product-card-price-row uk-flex uk-flex-middle">
                                                @if($product->combo_price > 0)
                                                    <span class="price-old">{{ $oldPrice }}đ</span>
                                                @endif
                                                <span class="price-current">{{ $price }}đ</span>
                                            </div>
                                            
                                            <div class="product-card-actions uk-flex uk-flex-middle">
                                                <a href="{{ $pCanonical }}" class="btn-buy-now">MUA NGAY</a>
                                                <a href="{{ $pCanonical }}" class="btn-cart">
                                                    <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="" class="cart-btn-icon">
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @endforeach
    @endif

    <!-- Khối Banner Dịch vụ Trang Chủ (Banner Home) -->
    @if(isset($slides['banner-home']['item']) && count($slides['banner-home']['item']))
        <section class="commit2-section banner-home-section">
            <div class="uk-container uk-container-center">
                <div class="commit2-grid">
                    @foreach($slides['banner-home']['item'] as $bannerHomeItem)
                        @php
                            $bannerUrl = !empty($bannerHomeItem['canonical']) ? write_url($bannerHomeItem['canonical']) : 'javascript:void(0);';
                            $target = (!empty($bannerHomeItem['window']) && $bannerHomeItem['window'] == '_blank') ? '_blank' : '_self';
                        @endphp
                        <div class="commit2-item">
                            <a href="{{ $bannerUrl }}" target="{{ $target }}" class="commit2-card-link">
                                <div class="commit2-card">
                                    <div class="commit2-img-container">
                                        <img src="{{ asset($bannerHomeItem['image']) }}" alt="{{ $bannerHomeItem['name'] }}" class="commit2-img">
                                    </div>
                                    <div class="commit2-overlay">
                                        <h3 class="commit2-title">{{ $bannerHomeItem['name'] }}</h3>
                                        <p class="commit2-desc">{{ $bannerHomeItem['alt'] ?? '' }}</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Khối Đối Tác và Khách Hàng Tiêu Biểu -->
    @if(isset($slides['partner']['item']) && count($slides['partner']['item']))
        <section class="partners-section">
            <div class="uk-container uk-container-center">
                <h2 class="partners-title">ĐỐI TÁC VÀ KHÁCH HÀNG TIÊU BIỂU</h2>
                <div class="partners-carousel uk-position-relative panel-partner">
                    <div class="swiper-container partner-swiper">
                        <div class="swiper-wrapper">
                            @foreach($slides['partner']['item'] as $partner)
                                <div class="swiper-slide partner-card">
                                    <div class="partner-logo-wrapper uk-flex uk-flex-center uk-flex-middle">
                                        <img src="{{ asset($partner['image']) }}" alt="{{ $partner['name'] ?? 'Partner' }}" class="partner-logo">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- Khối Tin Tức & Sự Kiện -->
    @if(isset($widgets['homepage-news']) && isset($widgets['homepage-news']->object) && $widgets['homepage-news']->object->isNotEmpty())
        @php
            $newsCat = $widgets['homepage-news']->object->first();
            $posts = isset($newsCat->posts) ? $newsCat->posts->take(4) : collect();
        @endphp
        @if($posts->isNotEmpty())
            <section class="homepage-news-section">
                <div class="uk-container uk-container-center">
                    <h2 class="news-section-title">TIN TỨC & SỰ KIỆN</h2>
                    <div class="news-grid">
                        @foreach($posts as $post)
                            @php
                                $postName = '';
                                if (isset($post->languages) && $post->languages->isNotEmpty()) {
                                    $firstLang = $post->languages->first();
                                    if (isset($firstLang->name)) {
                                        $postName = $firstLang->name;
                                    } elseif (isset($firstLang->pivot) && isset($firstLang->pivot->name)) {
                                        $postName = $firstLang->pivot->name;
                                    }
                                }
                                
                                $postCanonical = '#';
                                if (isset($post->languages) && $post->languages->isNotEmpty()) {
                                    $firstLang = $post->languages->first();
                                    if (isset($firstLang->canonical)) {
                                        $postCanonical = write_url($firstLang->canonical);
                                    } elseif (isset($firstLang->pivot) && isset($firstLang->pivot->canonical)) {
                                        $postCanonical = write_url($firstLang->pivot->canonical);
                                    }
                                }
                                
                                $postImage = !empty($post->image) ? asset($post->image) : asset('userfiles/image/product/oled_pro_x8s.png');
                                $postDate = date('d/m/Y', strtotime($post->created_at));
                            @endphp
                            <div class="news-card-wrapper">
                                <div class="news-card">
                                    <a href="{{ $postCanonical }}" class="news-image-link">
                                        <img src="{{ $postImage }}" alt="{{ $postName }}" class="news-img">
                                    </a>
                                    <div class="news-info">
                                        <div class="news-date">{{ $postDate }}</div>
                                        <h3 class="news-title">
                                            <a href="{{ $postCanonical }}" title="{{ $postName }}">{{ $postName }}</a>
                                        </h3>
                                        <a href="{{ $postCanonical }}" class="news-more-link">Xem chi tiết &gt;</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endif
@endsection
