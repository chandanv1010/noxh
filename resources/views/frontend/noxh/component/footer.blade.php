{{--
    Chan trang. Ban ve: noxh_image/tin-tuc-fix.webp (phan duoi cung).

    Mot hang nam khoi ngan nhau bang vach dung mo:
        thuong hieu | cac cot lien ket | Lien he | Ket noi voi chung toi
    roi mot dai duoi cung: dong ban quyen ben trai, khau hieu ben phai.

    Cot lien ket lay tu bang menus (nhom footer-menu) - moi muc CHA la mot
    cot, cac muc con la lien ket ben duoi. Nhom menu chua chia cap (tat ca
    cung nam o cap mot) thi cat doi lam hai cot khong tieu de, de ban cai cu
    khong bi vo bo cuc.

    Khoi "Lien he" KHONG lay tu menu ma doc thang Cau hinh he thong - so
    dien thoai va dia chi la thong tin he thong, bat quan tri them tay vao
    menu nua thi hai noi se lech nhau.
--}}
@php
    $lienKet = collect($menuChan ?? []);
    $coCap2 = $lienKet->contains(fn ($m) => count($m['children'] ?? []) > 0);

    $cotLienKet = $coCap2
        ? $lienKet->filter(fn ($m) => count($m['children'] ?? []) > 0)->values()
        : collect([
            ['name' => '', 'children' => $lienKet->take((int) ceil($lienKet->count() / 2))->values()->all()],
            ['name' => '', 'children' => $lienKet->slice((int) ceil($lienKet->count() / 2))->values()->all()],
        ]);

    $soHotline = nx_hotline_dau($system['contact_hotline'] ?? '');
    $soChuan = preg_replace('/\D/', '', (string) $soHotline);

    $mangXaHoi = array_filter([
        'facebook' => $system['social_facebook'] ?? '',
        'youtube' => $system['social_youtube'] ?? '',
        // Chua khai Zalo rieng thi dung chinh so hotline - zalo.me nhan so
        // dien thoai lam dia chi trang, khong phai bia them duong dan nao.
        'zalo' => ($system['social_zalo'] ?? '') ?: ($soChuan ? 'https://zalo.me/' . $soChuan : ''),
        'tiktok' => $system['social_tiktok'] ?? '',
    ]);

    $tenMang = [
        'facebook' => 'Facebook',
        'youtube' => 'YouTube',
        'zalo' => 'Zalo',
        'tiktok' => 'TikTok',
    ];

    $email = trim((string) ($system['contact_email'] ?? ''));
    $diaChi = trim((string) ($system['contact_address'] ?? $system['contact_office'] ?? ''));
    $coLienHe = $soHotline || $email !== '' || $diaChi !== '';
@endphp

<footer class="nx-footer">
    <div class="nx-footer__inner">
        <div class="nx-footer__cot nx-footer__cot--hieu">
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

        @if($coLienHe)
            <div class="nx-footer__cot nx-footer__cot--lienhe">
                <h3 class="nx-footer__title">{{ $intro['footer_contact_heading'] ?? 'Liên hệ' }}</h3>

                @if($soHotline)
                    <p class="nx-footer__contact">
                        @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $soHotline) }}">{{ $soHotline }}</a>
                    </p>
                @endif

                @if($email !== '')
                    <p class="nx-footer__contact">
                        @include('frontend.noxh.component.icon', ['name' => 'mail', 'size' => 15])
                        <a href="mailto:{{ $email }}">{{ $email }}</a>
                    </p>
                @endif

                @if($diaChi !== '')
                    <p class="nx-footer__contact">
                        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 15])
                        <span>{{ $diaChi }}</span>
                    </p>
                @endif
            </div>
        @endif

        @if(count($mangXaHoi))
            <div class="nx-footer__cot nx-footer__cot--mang">
                <h3 class="nx-footer__title">{{ $intro['footer_social_heading'] ?? 'Kết nối với chúng tôi' }}</h3>

                <div class="nx-footer__hang">
                    <div class="nx-footer__social">
                        @foreach($mangXaHoi as $ma => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="nx-footer__social--{{ $ma }}"
                               aria-label="{{ $tenMang[$ma] }}" title="{{ $tenMang[$ma] }}">
                                @include('frontend.noxh.component.social-icon', ['ten' => $ma])
                            </a>
                        @endforeach
                    </div>

                    {{-- Nut len dau trang. La mot lien ket chu khong phai nut
                         JS: khong co JS thi no van nhay ve dau trang duoc. --}}
                    <a href="#" class="nx-footer__len" aria-label="{{ $intro['footer_top_text'] ?? 'Lên đầu trang' }}"
                       title="{{ $intro['footer_top_text'] ?? 'Lên đầu trang' }}">
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-up', 'size' => 18])
                    </a>
                </div>
            </div>
        @endif
    </div>

    <div class="nx-footer__bottom">
        <div>
            <span>{{ $system['homepage_copyright'] ?? '© ' . date('Y') . ' NOXH.vn. All rights reserved.' }}</span>

            @if(!empty($intro['footer_slogan']))
                <span class="nx-footer__cau">
                    @include('frontend.noxh.component.icon', ['name' => 'heart', 'size' => 15])
                    {{ $intro['footer_slogan'] }}
                </span>
            @endif
        </div>
    </div>
</footer>
