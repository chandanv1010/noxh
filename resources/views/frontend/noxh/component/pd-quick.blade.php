{{--
    Khoi "Tu van nhanh cung NOXH.vn" o dau cot phai trang chi tiet.

    So dien thoai lay tu Hotline trong Cau hinh he thong - chi mot cho sua,
    doi so la ca website doi theo.

    Tham so: $system, $intro
--}}
@php
    $hotline = nx_hotline_dau($system['contact_hotline'] ?? '');
    $goi = $hotline !== '' ? 'tel:' . preg_replace('/[^0-9+]/', '', $hotline) : url('/lien-he');
@endphp

<section class="nx-pd-nhanh">
    <div class="nx-pd-nhanh__dau">
        <span class="nx-pd-nhanh__hinh">
            @include('frontend.noxh.component.icon', [
                'name' => $intro['projectlead_quick_icon'] ?? 'headset', 'size' => 34,
            ])
        </span>
        <div>
            <h2>{{ $intro['projectlead_quick_heading'] ?? 'Tư vấn nhanh cùng NOXH.vn' }}</h2>
            @if(!empty($intro['projectlead_quick_note']))
                <p>{{ $intro['projectlead_quick_note'] }}</p>
            @endif
        </div>
    </div>

    @if($hotline !== '')
        <a href="{{ $goi }}" class="nx-pd-nhanh__goi">
            @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 26])
            <span>
                <strong>{{ $hotline }}</strong>
                @if(!empty($intro['projectlead_quick_channel']))
                    <span>{{ $intro['projectlead_quick_channel'] }}</span>
                @endif
            </span>
            <i aria-hidden="true">
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 20])
            </i>
        </a>
    @endif
</section>
