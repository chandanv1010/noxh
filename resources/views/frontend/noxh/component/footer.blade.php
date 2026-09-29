{{--
    Chan trang. Cot lien ket lay tu bang menus (nhom footer-menu), thong tin
    lien he lay tu Cau hinh he thong.

    Tieu de tung cot chinh la ten muc CHA trong nhom footer-menu, cac muc con
    la lien ket ben duoi. Neu nhom menu chua chia cap (tat ca cung nam o cap
    mot) thi cat doi danh sach lam hai cot khong tieu de - de ban cai cu
    khong bi vo bo cuc.
--}}
@php
    $lienKet = collect($menuChan ?? []);
    $coCap2 = $lienKet->contains(fn ($m) => count($m['children'] ?? []) > 0);

    $cotLienKet = $coCap2
        ? $lienKet->filter(fn ($m) => count($m['children'] ?? []) > 0)->take(2)->values()
        : collect([
            ['name' => '', 'children' => $lienKet->take((int) ceil($lienKet->count() / 2))->values()->all()],
            ['name' => '', 'children' => $lienKet->slice((int) ceil($lienKet->count() / 2))->values()->all()],
        ]);

    $mangXaHoi = array_filter([
        'facebook' => $system['social_facebook'] ?? '',
        'youtube' => $system['social_youtube'] ?? '',
        'zalo' => $system['social_zalo'] ?? '',
        'tiktok' => $system['social_tiktok'] ?? '',
    ]);

    $tenMang = [
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'zalo' => 'Zalo',
        'tiktok' => 'TikTok',
    ];

    $soHotline = nx_hotline_dau($system['contact_hotline'] ?? '');
@endphp

<footer class="nx-footer">
    <div class="nx-footer__inner">
        <div class="nx-footer__cot">
            <div class="nx-footer__logo">
                @if(!empty($system['homepage_logo']))
                    <img src="{{ $system['homepage_logo'] }}" alt="{{ $system['homepage_company'] ?? 'NOXH.vn' }}">
                @else
                    <span class="nx-footer__mark">
                        @include('frontend.noxh.component.icon', ['name' => 'house', 'size' => 26])
                    </span>
                @endif
                <span>
                    <span class="nx-footer__brand">{{ $system['homepage_brand'] ?? 'NOXH.vn' }}</span>
                    <span class="nx-footer__tagline">{{ $intro['brand_tagline'] ?? '' }}</span>
                </span>
            </div>

            @if(!empty($intro['footer_description']))
                <p class="nx-footer__description">{{ $intro['footer_description'] }}</p>
            @endif
        </div>

        @foreach($cotLienKet as $cot)
            @continue(!count($cot['children']))
            <div class="nx-footer__cot">
                @if($cot['name'] !== '')
                    <h3 class="nx-footer__title">{{ $cot['name'] }}</h3>
                @endif
                <ul class="nx-footer__list">
                    @foreach($cot['children'] as $muc)
                        <li><a href="{{ $muc['url'] }}">{{ $muc['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        @if(count($mangXaHoi))
            <div class="nx-footer__cot">
                <h3 class="nx-footer__title">{{ $intro['footer_social_heading'] ?? 'Kết nối với chúng tôi' }}</h3>
                <div class="nx-footer__social">
                    @foreach($mangXaHoi as $ma => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener"
                           class="nx-footer__social--{{ $ma }}"
                           aria-label="{{ $tenMang[$ma] }}" title="{{ $tenMang[$ma] }}">
                            @include('frontend.noxh.component.social-icon', ['ten' => $ma])
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($soHotline)
            <div class="nx-footer__cot">
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $soHotline) }}" class="nx-footer__phone">
                    @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 24])
                    <span>
                        <strong>{{ $soHotline }}</strong>
                        <span>{{ $intro['header_phone_note'] ?? 'Tư vấn miễn phí 24/7' }}</span>
                    </span>
                </a>
            </div>
        @endif
    </div>

    <div class="nx-footer__bottom">
        <div>
            <span>{{ $system['homepage_copyright'] ?? '© ' . date('Y') . ' NOXH.vn. All rights reserved.' }}</span>
            @if(!empty($intro['footer_slogan']))
                <span>{{ $intro['footer_slogan'] }}</span>
            @endif
        </div>
    </div>
</footer>
