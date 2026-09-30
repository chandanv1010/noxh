{{--
    Khoi chuyen gia o cot phai. Du lieu nguoi do NoxhComposer cap, cac dong
    chu co dinh lay tu nhom "Thẻ chuyên gia" trong man hinh Giới thiệu.

    Ban ve noxh_image/tin-tuc-fix.webp xep the nay thanh ba dong: nhan co dinh
    (TU VAN CUNG CHUYEN GIA), TEN nguoi, roi chuc danh. Truoc day ten nguoi bi
    dung lam nhan nen the doc ra thanh "CONG HOA / Chuyen gia tu van..." -
    khong con biet ai dang tu van.
--}}
@if($chuyenGia)
    @php
        $nutChuyenGia = trim((string) ($intro['expert_button'] ?? ''));
        $linkChuyenGia = trim((string) ($intro['expert_button_link'] ?? '')) ?: '/cong-hoa/tu-van';
    @endphp

    <div class="nx-expert" style="margin-bottom:18px">
        @if(!empty($intro['expert_heading']))
            <h2 class="nx-expert__title">{{ $intro['expert_heading'] }}</h2>
        @endif

        <p class="nx-expert__nguoi">{{ $chuyenGia->name }}</p>

        @if($chuyenGia->title)
            <p class="nx-expert__name">{{ $chuyenGia->title }}</p>
        @endif

        @if($chuyenGia->commitments)
            <ul class="nx-expert__list">
                @foreach(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $chuyenGia->commitments))) as $ck)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16])
                        {{ $ck }}
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Quan tri dien chu cho nut thi hien nut dang ky; de trong thi quay
             ve nut goi so hotline nhu truoc. --}}
        @if($nutChuyenGia !== '')
            <a href="{{ url($linkChuyenGia) }}" class="nx-btn nx-btn--sm">{{ $nutChuyenGia }}</a>
        @elseif($chuyenGia->phone)
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $chuyenGia->phone) }}" class="nx-btn nx-btn--sm">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                {{ $chuyenGia->phone }}
            </a>
        @endif

        @if($chuyenGia->image)
            <div class="nx-expert__photo">
                <img src="{{ $chuyenGia->image }}" alt="{{ $chuyenGia->name }}" loading="lazy">
            </div>
        @endif
    </div>
@endif
