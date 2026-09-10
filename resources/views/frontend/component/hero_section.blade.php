<section class="home-hero-section">
    <div class="uk-container uk-container-center">
        <div class="hero-grid">
            <!-- Cột Trái: Menu Dọc -->
            <div class="hero-sidebar-col uk-visible-large">
                <div class="vertical-menu-wrapper">
                    @if(isset($menu['main-menu_array']) && count($menu['main-menu_array']))
                        <ul class="vertical-menu-list uk-list uk-clearfix">
                            @foreach($menu['main-menu_array'] as $menuVal)
                                @php
                                    $mItem = $menuVal['item'];
                                    $mName = $mItem->languages->first()->pivot->name ?? '';
                                    $mCanonical = ($mName == 'Trang chủ') ? '.' : write_url($mItem->languages->first()->pivot->canonical ?? '');
                                    $mIcon = $mItem->icon;
                                    $hasChildren = count($menuVal['children']) > 0;
                                @endphp
                                <li class="vertical-menu-item {{ $hasChildren ? 'has-children' : '' }}">
                                    <a href="{{ $mCanonical }}" class="uk-flex uk-flex-middle uk-flex-space-between">
                                        <span class="uk-flex uk-flex-middle menu-link-content">
                                            @if(!empty($mIcon))
                                                <img src="{{ asset($mIcon) }}" alt="" class="menu-item-icon">
                                            @endif
                                            <span class="menu-item-name">{{ $mName }}</span>
                                        </span>
                                        <i class="fa fa-angle-right arrow-icon"></i>
                                    </a>
                                    @if($hasChildren)
                                        <div class="submenu-container">
                                            <div class="submenu-grid">
                                                @foreach($menuVal['children'] as $childVal)
                                                    @php
                                                        $cItem = $childVal['item'];
                                                        $cName = $cItem->languages->first()->pivot->name ?? '';
                                                        $cCanonical = write_url($cItem->languages->first()->pivot->canonical ?? '');
                                                        $hasSubChildren = count($childVal['children']) > 0;
                                                    @endphp
                                                    <div class="submenu-col">
                                                        <a href="{{ $cCanonical }}" class="submenu-l2-title">{{ $cName }}</a>
                                                        @if($hasSubChildren)
                                                            <ul class="submenu-l3-list uk-list uk-clearfix">
                                                                @foreach($childVal['children'] as $subChildVal)
                                                                    @php
                                                                        $scItem = $subChildVal['item'];
                                                                        $scName = $scItem->languages->first()->pivot->name ?? '';
                                                                        $scCanonical = write_url($scItem->languages->first()->pivot->canonical ?? '');
                                                                    @endphp
                                                                        <li class="submenu-l3-item">
                                                                            <a href="{{ $scCanonical }}">{{ $scName }}</a>
                                                                        </li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                            <!-- Xem tất cả các danh mục -->
                            <li class="vertical-menu-item view-all-categories">
                                <a href="tim-kiem/trang-1" class="uk-flex uk-flex-middle">
                                    <i class="fa fa-bars menu-grid-icon"></i>
                                    <span class="menu-item-name">Xem tất cả các danh mục</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Cột Phải: Slide chính + Cam kết chất lượng -->
            <div class="hero-main-col">
                <!-- Slide -->
                @include('frontend.component.slide')

                <!-- Khối dưới slide: 3 cam kết từ slide keyword commit -->
                @if(isset($slides['commit']['item']) && count($slides['commit']['item']))
                    <div class="commit-banners-row">
                        <div class="uk-grid uk-grid-medium commit-grid">
                            @foreach($slides['commit']['item'] as $commitItem)
                                <div class="uk-width-large-1-3 uk-width-medium-1-3 uk-width-small-1-1">
                                    <div class="commit-card uk-flex uk-flex-middle">
                                        <img src="{{ asset($commitItem['image']) }}" alt="{{ $commitItem['name'] }}" class="commit-icon">
                                        <div class="commit-text">
                                            <div class="commit-title">{{ $commitItem['name'] }}</div>
                                            @if(!empty($commitItem['alt']))
                                                <div class="commit-desc">{{ $commitItem['alt'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
