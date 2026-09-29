{{--
    Mot dong nguoi tu van. Dung o ca khoi cot phai lan hop "Xem them tu van
    vien khac" - hai cho phai giong het nhau nen chi viet mot lan.

    Chuc danh mac dinh va dong khu vuc tu doc tu Cau hinh chung ngay tai day
    chu khong nhan tu ben ngoai: component nay duoc goi tu ca khoi cot phai
    lan hop bat len, truyen tay thi mot trong hai cho se quen.

    Tham so: $nv (User), $tenTinh, $intro
--}}
@php
    $thayNv = ['{tinh}' => $tenTinh ?: 'khu vực dự án'];
    $chucDanh = trim((string) ($intro['projectlead_staff_role'] ?? ''));
    $khuVuc = trim(strtr((string) ($intro['projectlead_staff_area'] ?? ''), $thayNv));
@endphp
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

    {{-- Popup lien he do component advisor-modal lo, chi in mot lan cho ca trang. --}}
    <button type="button" class="nx-btn nx-btn--sm nx-pd-tuvan__nut"
            data-nx-lien-he="{{ $nv->id }}" data-nx-ten="{{ $nv->name }}">
        @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 17])
        {{ $intro['projectlead_staff_button'] ?? 'Liên hệ' }}
    </button>
</li>
