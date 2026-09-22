{{--
    Danh sach tu van ho so - the nhan vien kinh doanh kem nut "Lien he".

    Tham so:
      $nhanVien  - Collection User, bat buoc
      $cot       - so cot tren man hinh rong (mac dinh 3)

    Nut chi mang theo id va ten; popup do component advisor-modal lo, va chi
    can in mot lan cho ca trang.
--}}
@if(isset($nhanVien) && $nhanVien->count())
    <div class="nx-advisors nx-advisors--{{ $cot ?? 3 }}">
        @foreach($nhanVien as $nv)
            <div class="nx-advisor">
                <div class="nx-advisor__anh">
                    @if($nv->image)
                        <img src="{{ $nv->image }}" alt="{{ $nv->name }}" loading="lazy">
                    @else
                        @include('frontend.noxh.component.icon', ['name' => 'user', 'size' => 24])
                    @endif
                </div>

                <div class="nx-advisor__than">
                    <p class="nx-advisor__ten">{{ $nv->name }}</p>
                    <p class="nx-advisor__chuc">{{ $nv->title ?: 'Tư vấn hồ sơ NOXH' }}</p>
                    @if($nv->address)
                        <p class="nx-advisor__khu">Khu vực: {{ $nv->address }}</p>
                    @endif
                </div>

                <button type="button" class="nx-btn nx-btn--sm nx-advisor__nut"
                        data-nx-lien-he="{{ $nv->id }}" data-nx-ten="{{ $nv->name }}">
                    @include('frontend.noxh.component.icon', ['name' => 'send', 'size' => 14])
                    Liên hệ
                </button>
            </div>
        @endforeach
    </div>
@endif
