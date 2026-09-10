<header class="tazen-header">
    @include('components.top-search')

    <!-- TOP BAR -->
    <div class="header-top-bar">
        <div class="uk-container uk-container-center">
            <div class="uk-flex uk-flex-middle uk-flex-space-between top-bar-flex">
                <!-- Left Group -->
                <div class="top-bar-left uk-flex uk-flex-middle">
                    <div class="top-bar-item hotline">
                        <img src="{{ asset('vendor/frontend/img/project/icons/Group 9865.png') }}" alt="" class="top-bar-icon">
                        <span>Tổng đài tư vấn: <strong>{{ $system['contact_hotline'] ?? '0987 872 872' }}</strong></span>
                    </div>
                    <div class="top-bar-item support">
                        <a href="tel:{{ $system['contact_hotline'] ?? '0987 872 872' }}" class="top-link uk-flex uk-flex-middle">
                            <img src="{{ asset('vendor/frontend/img/project/icons/Group 9866.png') }}" alt="" class="top-bar-icon">
                            <span>Hỗ trợ kỹ thuật 24/7</span>
                        </a>
                    </div>
                </div>

                <!-- Middle Group (Centered) -->
                <div class="top-bar-middle uk-flex uk-flex-middle">
                    <div class="top-bar-item map">
                        <img src="{{ asset('vendor/frontend/img/project/icons/Group 9882.png') }}" alt="" class="top-bar-icon">
                        <span>Hệ thống lắp đặt toàn quốc</span>
                    </div>
                    <div class="top-bar-item delivery">
                        <img src="{{ asset('vendor/frontend/img/project/icons/c-1.png') }}" alt="" class="top-bar-icon yellow-filter">
                        <span>Giao hàng nhanh</span>
                    </div>
                    <div class="top-bar-item warranty">
                        <img src="{{ asset('vendor/frontend/img/project/icons/Group 9884.png') }}" alt="" class="top-bar-icon">
                        <span>Bảo hành chính hãng</span>
                    </div>
                </div>

                <!-- Right Group -->
                <div class="top-bar-right uk-flex uk-flex-middle">
                    <a href="/tin-tuc.html" title="Tin tức" class="top-link">Tin tức</a>
                    <span class="divider">|</span>
                    <a href="/lien-he.html" title="Liên hệ" class="top-link">Liên hệ</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN HEADER -->
    <div class="header-main-bar">
        <div class="uk-container uk-container-center header-container">
            <div class="uk-flex uk-flex-middle main-bar-flex">
                <!-- Logo -->
                <div class="logo">
                    <a href="/" title="{{ $system['homepage_brand'] ?? 'TRUC+' }}">
                        <img src="{{ $system['homepage_logo'] ?? asset('userfiles/image/logo/logo.png') }}" alt="{{ $system['homepage_brand'] ?? 'TRUC+' }}">
                    </a>
                </div>

                <!-- Category Dropdown Button (Visible Large) -->
                <div class="category-dropdown-wrapper uk-visible-large">
                    <button class="category-btn uk-flex uk-flex-middle">
                        <img src="{{ asset('vendor/frontend/img/project/icons/menu bar/Group 9889.png') }}" alt="" class="btn-icon">
                        <span>DANH MỤC SẢN PHẨM</span>
                    </button>
                    @if(isset($category) && count($category))
                        <div class="category-dropdown">
                            <ul class="category-list uk-list">
                                @foreach($category as $catVal)
                                    @php
                                        $catName = $catVal['item']->languages->first()->pivot->name;
                                        $catCanonical = write_url($catVal['item']->languages->first()->pivot->canonical);
                                        $catIcon = $catVal['item']->icon;
                                    @endphp
                                    <li>
                                        <a href="{{ $catCanonical }}" class="uk-flex uk-flex-middle">
                                            @if($catIcon)
                                                <img src="{{ $catIcon }}" alt="" class="cat-icon">
                                            @endif
                                            <span class="cat-name">{{ $catName }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Search Form (Visible Large) -->
                <div class="header-search-form uk-visible-large">
                    <form action="/tim-kiem" method="get" class="search-form uk-flex">
                        <input type="text" name="keyword" class="search-input" placeholder="Tìm kiếm sản phẩm, giải pháp..." value="{{ request('keyword') }}">
                        <button type="submit" class="search-submit uk-flex uk-flex-center uk-flex-middle">
                            <i class="fa fa-search"></i>
                        </button>
                    </form>
                </div>

                <!-- Right Utilities (Visible Large) -->
                <div class="right-utilities uk-visible-large uk-flex uk-flex-middle">
                    <!-- Hotline Bán hàng -->
                    <div class="header-util-item sales-hotline uk-flex uk-flex-middle">
                        <img src="{{ asset('vendor/frontend/img/call.svg') }}" alt="" class="util-icon yellow-filter">
                        <div class="util-text">
                            <span class="util-label">BÁN HÀNG ONLINE</span>
                            <a href="tel:{{ $system['contact_phone'] }}" class="util-value">{{ $system['contact_phone'] }}</a>
                        </div>
                    </div>

                    <!-- Account -->
                    <div class="header-util-item user-account uk-flex uk-flex-middle">
                        <img src="{{ asset('vendor/frontend/img/project/icons/menu bar/Vector-2.png') }}" alt="" class="util-icon">
                        <div class="util-text">
                            @if(Auth::guard('customer')->check())
                                <span class="util-label">TÀI KHOẢN</span>
                                <a href="{{ route('customer.account') }}" class="util-value">{{ Auth::guard('customer')->user()->name }}</a>
                            @else
                                <span class="util-label">TÀI KHOẢN</span>
                                <a href="https://gps.truc.com.vn/" target="_blank" class="util-value">Đăng nhập/Đăng ký</a>
                            @endif
                        </div>
                    </div>

                    <!-- Cart -->
                    <div class="header-cart-wrapper">
                        <a href="{{ route('cart.checkout') }}" class="cart-link uk-flex uk-flex-middle">
                            <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="" class="cart-icon">
                        </a>
                    </div>
                </div>

                <!-- Mobile Right Actions (Cart Icon + Hamburger Menu Button) -->
                <div class="mobile-right-actions uk-hidden-large uk-flex uk-flex-middle">
                    <a href="{{ route('cart.checkout') }}" class="mobile-cart-link" title="Giỏ hàng">
                        <div class="cart-icon-relative">
                            <img src="{{ asset('vendor/frontend/img/project/icons/Group 9890.png') }}" alt="Giỏ hàng" class="mobile-cart-icon">
                            <span class="mobile-cart-badge cart-count">{{ Cart::instance('shopping')->count() }}</span>
                        </div>
                    </a>
                    <a class="mobile-menu-btn" href="#offcanvas" data-uk-offcanvas="{target:'#offcanvas'}">
                        <i class="fa fa-bars"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Mobile Menu Offcanvas -->
<div id="offcanvas" class="uk-offcanvas">
    <div class="uk-offcanvas-bar uk-offcanvas-bar-flip mobile-menu-offcanvas">
        <button class="uk-offcanvas-close mobile-menu-close" type="button">
            <i class="fa fa-times"></i>
        </button>
        
        <div class="mobile-menu-header">
            <div class="mobile-menu-logo">
                <a href="/" title="Logo">
                    <img src="{{ $system['homepage_logo'] ?? asset('userfiles/image/logo/logo.png') }}" alt="Logo" />
                </a>
            </div>
        </div>

        <nav class="mobile-menu-nav">
            <ul class="uk-nav uk-nav-offcanvas mobile-menu-list">
                @if(isset($category) && count($category))
                    @foreach($category as $catVal)
                        @php
                            $catName = $catVal['item']->languages->first()->pivot->name;
                            $catCanonical = write_url($catVal['item']->languages->first()->pivot->canonical);
                        @endphp
                        <li><a href="{{ $catCanonical }}">{{ $catName }}</a></li>
                    @endforeach
                @endif
                <li><a href="/tin-tuc.html">Tin tức</a></li>
                <li><a href="/lien-he.html">Liên hệ</a></li>
            </ul>
        </nav>
    </div>
</div>