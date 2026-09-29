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
                @include('frontend.noxh.component.pd-advisor-item', ['nv' => $nv])
            @endforeach
        </ul>

        {{-- Chi moi bam sang hop day du khi CON nguoi chua hien ra o day. --}}
        @if(!empty($intro['projectlead_staff_more']) && ($tatCaNhanVien ?? $nhanVien)->count() > $nhanVien->count())
            {{-- Bam vao thi bat hop liet ke DAY DU nguoi phu trach du an nay,
                 khong phai nhay sang trang doi tu van chung cua website. --}}
            <button type="button" class="nx-pd-tuvan__them" data-nx-mo="tu-van-day">
                {{ $intro['projectlead_staff_more'] }}
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 15])
            </button>
        @endif
    @else
        <p class="nx-pd-tuvan__trong">
            {{ $intro['projectlead_staff_empty'] ?? 'Dự án đang được phân công chuyên viên phụ trách.' }}
        </p>
    @endif
</section>
