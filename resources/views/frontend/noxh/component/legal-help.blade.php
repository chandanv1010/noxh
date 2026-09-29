{{--
    The "CAN HO TRO PHAP LY?" o goc phai dai dau trang.

    Ten, anh, loi cam ket va so dien thoai lay tu chuyen vien tu van mac dinh
    ($chuyenGia, do NoxhComposer cap) chu khong viet cung: doi nguoi phu trach
    o man hinh "Chuyên gia" la trang ngoai doi theo.

    Tham so: $intro, $chuyenGia
--}}
@php
    $camKet = array_values(array_filter(array_map(
        'trim',
        preg_split('/\r\n|\r|\n/', (string) ($chuyenGia->commitments ?? '')) ?: []
    ), fn ($d) => $d !== ''));

    $nutLink = trim((string) ($intro['legal_help_button_link'] ?? '')) ?: '/cong-hoa/tu-van';
    $soDienThoai = trim((string) ($chuyenGia->phone ?? ''));

    // Anh rieng cua the (da tach nen) uu tien hon anh chan dung cua chuyen
    // vien: anh chan dung con nen trang thi dan vao goc the se lo mot khoi.
    $anhNguoi = trim((string) ($intro['legal_help_image'] ?? '')) ?: trim((string) ($chuyenGia->image ?? ''));
@endphp

<div class="nx-lh{{ $anhNguoi !== '' ? ' co-anh' : '' }}">
    <div class="nx-lh__chu">
        <h2 class="nx-lh__tieude">{{ $intro['legal_help_heading'] ?? 'CẦN HỖ TRỢ PHÁP LÝ?' }}</h2>

        @if(!empty($chuyenGia?->description))
            <p class="nx-lh__mo-ta">{{ $chuyenGia->description }}</p>
        @endif

        @if(count($camKet))
            <ul class="nx-lh__list">
                @foreach($camKet as $ck)
                    <li>
                        @include('frontend.noxh.component.icon', [
                            'name' => $intro['legal_help_bullet_icon'] ?? 'check-circle',
                            'size' => 18,
                        ])
                        {{ $ck }}
                    </li>
                @endforeach
            </ul>
        @endif

        <a href="{{ url($nutLink) }}" class="nx-lh__nut">
            @include('frontend.noxh.component.icon', [
                'name' => $intro['legal_help_button_icon'] ?? 'sms',
                'size' => 20,
            ])
            {{ $intro['legal_help_button'] ?? 'TƯ VẤN MIỄN PHÍ' }}
        </a>
    </div>

    @if($soDienThoai !== '')
        <div class="nx-lh__goi">
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $soDienThoai) }}">
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 22])
                {{ $soDienThoai }}
            </a>
            @if(!empty($intro['legal_help_phone_note']))
                <span>{{ $intro['legal_help_phone_note'] }}</span>
            @endif
        </div>
    @endif

    {{-- Anh nguoi tu van dat sau cung: tren dien thoai thi an di cho do chat. --}}
    @if($anhNguoi !== '')
        <div class="nx-lh__anh">
            <img src="{{ $anhNguoi }}" alt="{{ $chuyenGia->name ?? '' }}" loading="lazy">
        </div>
    @endif
</div>
