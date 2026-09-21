{{--
    Chan trang. Cot lien ket lay tu bang menus (nhom footer-menu), thong tin
    lien he lay tu Cau hinh he thong.
--}}
@php
    // Chia cot lien ket thanh hai cot cho can, thay vi mot cot dai loong ngoong.
    $lienKet = collect($menuChan ?? []);
    $nua = (int) ceil($lienKet->count() / 2);

    $mangXaHoi = array_filter([
        'Facebook' => $system['social_facebook'] ?? '',
        'YouTube' => $system['social_youtube'] ?? '',
        'Zalo' => $system['social_zalo'] ?? '',
        'TikTok' => $system['social_tiktok'] ?? '',
    ]);
@endphp

<footer class="nx-footer">
    <div class="nx-footer__inner">
        <div>
            <div class="nx-footer__brand">NOXH<span style="opacity:.75">.vn</span></div>
            <div style="font-size:12px;opacity:.8">{{ $intro['brand_tagline'] ?? 'Rõ pháp lý – Đúng thông tin' }}</div>

            <p class="nx-footer__description">
                {{ $intro['footer_description'] ?? 'NOXH.vn là cổng thông tin nhà ở xã hội uy tín, minh bạch và cập nhật liên tục trên toàn quốc.' }}
            </p>

            @if(count($mangXaHoi))
                <div class="nx-footer__social">
                    @foreach($mangXaHoi as $ten => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $ten }}" title="{{ $ten }}">
                            @include('frontend.noxh.component.icon', ['name' => 'globe', 'size' => 16])
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div>
            <h3 class="nx-footer__title">Liên kết nhanh</h3>
            <ul class="nx-footer__list">
                @foreach($lienKet->take($nua) as $muc)
                    <li><a href="{{ $muc['url'] }}">{{ $muc['name'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="nx-footer__title">Hỗ trợ</h3>
            <ul class="nx-footer__list">
                @foreach($lienKet->slice($nua) as $muc)
                    <li><a href="{{ $muc['url'] }}">{{ $muc['name'] }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="nx-footer__title">Liên hệ</h3>

            @if(!empty($system['contact_hotline']))
                <div class="nx-footer__contact">
                    @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                    <span>
                        @foreach(array_filter(array_map('trim', explode('|', $system['contact_hotline']))) as $i => $so)
                            @if($i > 0)<span aria-hidden="true">·</span>@endif
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $so) }}">{{ $so }}</a>
                        @endforeach
                    </span>
                </div>
            @endif

            @if(!empty($system['contact_email']))
                <div class="nx-footer__contact">
                    @include('frontend.noxh.component.icon', ['name' => 'mail', 'size' => 15])
                    <a href="mailto:{{ $system['contact_email'] }}">{{ $system['contact_email'] }}</a>
                </div>
            @endif

            @if(!empty($system['contact_address']))
                <div class="nx-footer__contact">
                    @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 15])
                    <span>{{ $system['contact_address'] }}</span>
                </div>
            @endif

            @if(!empty($system['contact_working_hours']))
                <div class="nx-footer__contact">
                    @include('frontend.noxh.component.icon', ['name' => 'clock', 'size' => 15])
                    <span>{{ $system['contact_working_hours'] }}</span>
                </div>
            @endif
        </div>
    </div>

    <div class="nx-footer__bottom">
        <div>
            <span>{{ $system['homepage_copyright'] ?? '© ' . date('Y') . ' NOXH.vn. All rights reserved.' }}</span>
            <span>Vì cộng đồng – Vì một Việt Nam an cư</span>
        </div>
    </div>
</footer>
