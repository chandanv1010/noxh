<footer class="footer">
    <div class="uk-container uk-container-center">
        <!-- Flex grid instead of UIkit grid to guarantee columns stay inline and centered correctly -->
        <div class="footer-main-flex">
            <!-- Left Info Column -->
            <div class="footer-info-col">
                <div class="footer-logo">
                    <img src="{{ $system['homepage_logo'] ?? asset('userfiles/image/logo/logo.png') }}" alt="{{ $system['homepage_brand'] ?? 'TRUC GPS' }}">
                </div>

                @if(!empty($system['contact_signature']))
                    <div class="footer-signature-content">
                        {!! preg_replace('/<img[^>]*>/i', '', $system['contact_signature']) !!}
                    </div>
                @else
                    <ul class="footer-contact-list uk-list">
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
            
            <!-- Middle Group: Contains the 3 menus and the Support Banner below them -->
            <div class="footer-middle-group">
                <div class="footer-menus-row">
                    @if(isset($menu['footer-menu']) && count($menu['footer-menu']))
                        @foreach($menu['footer-menu'] as $footerCol)
                            @php
                                $colName = $footerCol['item']->languages->first()->pivot->name ?? '';
                                $colCanonical = $footerCol['item']->languages->first()->pivot->canonical ?? '';
                            @endphp
                            @if(mb_strtolower(trim($colName)) === 'dịch vụ' || mb_strtolower(trim($colName)) === 'dich vu' || strpos($colCanonical, 'dich-vu') !== false)
                                @continue
                            @endif
                            <div class="footer-col">
                                <h3 class="footer-col-title">{{ $colName }}</h3>
                                <ul class="footer-col-list uk-list">
                                    @foreach($footerCol['children'] as $footerChild)
                                        @php
                                            $childName = $footerChild['item']->languages->first()->pivot->name ?? '';
                                            $childUrl = write_url($footerChild['item']->languages->first()->pivot->canonical ?? '');
                                        @endphp
                                        <li><a href="{{ $childUrl }}" title="{{ $childName }}">{{ $childName }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    @endif
                </div>
                
                <!-- Yellow Support Banner (positioned inside footer-middle-group, centered under 3 menus) -->
                <div class="footer-yellow-banner uk-flex uk-flex-middle uk-flex-space-between">
                    <div class="banner-item">
                        <span>Phản ánh khiếu nại:</span>
                        <strong>{{ !empty($system['contact_complaint']) ? $system['contact_complaint'] : '0377622266 - 0399622266' }}</strong>
                    </div>
                    <div class="banner-item">
                        <span>Hỗ trợ kỹ thuật:</span>
                        <strong>{{ !empty($system['contact_technical']) ? $system['contact_technical'] : '0764622266 - 0765622266' }}</strong>
                    </div>
                    <div class="banner-item">
                        <span>Thời gian:</span>
                        <strong>{{ !empty($system['contact_working_hours']) ? $system['contact_working_hours'] : '7h đến 23h' }}</strong>
                    </div>
                </div>
            </div>
            
            <!-- Right Newsletter & Social Column (20%) -->
            <div class="footer-newsletter-col">
                <h3 class="footer-col-title">ĐĂNG KÝ NHẬN TIN</h3>
                
                <form action="/subscribe" method="post" class="footer-newsletter-form">
                    @csrf
                    <input type="email" name="email" class="newsletter-input" placeholder="Nhập email...">
                    <button type="submit" class="newsletter-btn">ĐĂNG KÝ</button>
                </form>
                
                <div class="footer-socials uk-flex uk-flex-middle">
                    <a href="{{ $system['social_facebook'] ?? '#' }}" class="social-icon" target="_blank" title="Facebook"><i class="fa fa-facebook-f"></i></a>
                    <a href="{{ $system['social_instagram'] ?? '#' }}" class="social-icon" target="_blank" title="Instagram"><i class="fa fa-instagram"></i></a>
                    <a href="{{ $system['social_google'] ?? '#' }}" class="social-icon" target="_blank" title="Google"><i class="fa fa-google"></i></a>
                    <a href="{{ $system['social_tiktok'] ?? '#' }}" class="social-icon" target="_blank" title="TikTok">
                        <svg width="13" height="13" viewBox="0 0 448 512" fill="currentColor">
                            <path d="M448 209.91a210.06 210.06 0 0 1-122.77-39.25V349.38A162.55 162.55 0 1 1 185 188.31V258.1a92.44 92.44 0 1 0 70.36 89.47V0h69.75a140.39 140.39 0 0 0 122.89 123.63z"/>
                        </svg>
                    </a>
                    <a href="{{ $system['social_youtube'] ?? '#' }}" class="social-icon" target="_blank" title="YouTube"><i class="fa fa-youtube-play"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Full-width copyright bar at the bottom -->
    <div class="footer-copyright-bar">
        <div class="uk-container uk-container-center uk-text-center">
            <span>{{ $system['contact_copyright'] ?? 'Bản quyền thuộc về TRUC GPS - Website thương mại điện tử đã được Bộ Công Thương cấp phép' }}</span>
        </div>
    </div>
</footer>

<div id="fb-root"></div>