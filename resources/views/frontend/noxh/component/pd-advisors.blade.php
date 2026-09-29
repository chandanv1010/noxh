{{--
    Khoi "DANH SACH TU VAN HO TRO" o cot phai trang chi tiet du an.

    Danh sach nguoi o day la NHUNG NGUOI DUOC GAN VAO CHINH DU AN NAY
    (bang product_user, gan o man hinh sua du an) - khong phai danh sach
    tu van vien mac dinh cua website. Du an chua gan ai thi khoi hien mot
    dong moi de lai so dien thoai, van tot hon la mot danh sach nguoi
    khong lien quan.

    Nut "Lien he" mo popup advisor-modal, gan thang lead cho dung nguoi do.

    Tham so: $nhanVien, $duAn, $tenTinh, $intro
--}}
@php
    $thay = ['{tinh}' => $tenTinh ?: 'khu vực dự án'];
    $moTa = trim(strtr((string) ($intro['projectlead_staff_note'] ?? ''), $thay));
    $khuVuc = trim(strtr((string) ($intro['projectlead_staff_area'] ?? ''), $thay));
    $chucDanh = $intro['projectlead_staff_role'] ?? '';
@endphp

<section class="nx-pd-tuvan" id="tu-van">
    <h2 class="nx-pd-tuvan__tieude">
        {{ $intro['projectlead_staff_heading'] ?? 'DANH SÁCH TƯ VẤN HỖ TRỢ' }}
    </h2>

    @if($moTa !== '')
        <p class="nx-pd-tuvan__mo">{{ $moTa }}</p>
    @endif

    @if(!empty($intro['projectlead_staff_verify']))
        <p class="nx-pd-tuvan__xac">{{ $intro['projectlead_staff_verify'] }}</p>
    @endif

    @if($nhanVien->count())
        <ul class="nx-pd-tuvan__ds">
            @foreach($nhanVien as $nv)
                <li>
                    <span class="nx-pd-tuvan__anh">
                        @include('frontend.noxh.component.avatar', [
                            'ten' => $nv->name, 'anh' => $nv->image, 'co' => 52,
                        ])
                    </span>

                    <span class="nx-pd-tuvan__chu">
                        <strong>{{ $nv->name }}</strong>
                        @if($nv->title || $chucDanh !== '')
                            <span>{{ $nv->title ?: $chucDanh }}</span>
                        @endif
                        @if($khuVuc !== '')
                            <span>{{ $khuVuc }}</span>
                        @endif
                    </span>

                    {{-- Popup do component advisor-modal lo, chi in mot lan
                         cho ca trang. --}}
                    <button type="button" class="nx-btn nx-btn--sm nx-pd-tuvan__nut"
                            data-nx-lien-he="{{ $nv->id }}" data-nx-ten="{{ $nv->name }}">
                        @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                        {{ $intro['projectlead_staff_button'] ?? 'Liên hệ' }}
                    </button>
                </li>
            @endforeach
        </ul>

        @if(!empty($intro['projectlead_staff_more']))
            <a href="{{ route('noxh.page.advise') }}" class="nx-pd-tuvan__them">
                {{ $intro['projectlead_staff_more'] }}
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
            </a>
        @endif
    @else
        <p class="nx-pd-tuvan__trong">
            {{ $intro['projectlead_staff_empty'] ?? 'Dự án đang được phân công chuyên viên phụ trách.' }}
        </p>
    @endif
</section>
