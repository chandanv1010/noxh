{{--
    Nhan vien kinh doanh phu trach du an.

    Du lieu lay tu chinh bang users - nguoi ban hang tu cap nhat anh, chuc danh
    va so dien thoai trong bang dieu khien /sale cua ho, khong phai nho quan tri
    nhap ho.

    Tham so:
      $nhanVien - Collection User, co the rong (khi do khong in gi ca)
--}}
@if(isset($nhanVien) && $nhanVien->count())
    <div class="nx-panel nx-staff">
        <h2 class="nx-panel__title">Nhân viên kinh doanh phụ trách</h2>

        <ul class="nx-staff__list">
            @foreach($nhanVien as $nv)
                @php
                    // Zalo de trong thi dung so dien thoai - phan lon truong hop
                    // hai so la mot, bat nhap hai lan chi tao co hoi nhap lech.
                    $soZalo = preg_replace('/[^0-9]/', '', (string) ($nv->zalo ?: $nv->phone));
                    $soGoi = preg_replace('/[^0-9+]/', '', (string) $nv->phone);
                @endphp
                <li class="nx-staff__item">
                    <div class="nx-staff__avatar">
                        @if($nv->image)
                            <img src="{{ $nv->image }}" alt="{{ $nv->name }}" loading="lazy">
                        @else
                            @include('frontend.noxh.component.icon', ['name' => 'user', 'size' => 26])
                        @endif
                    </div>

                    <div class="nx-staff__body">
                        <p class="nx-staff__name">{{ $nv->name }}</p>
                        @if($nv->title)
                            <p class="nx-staff__role">{{ $nv->title }}</p>
                        @endif

                        @if($nv->description)
                            <p class="nx-staff__note">{{ $nv->description }}</p>
                        @endif

                        <div class="nx-staff__links">
                            @if($nv->phone)
                                <a href="tel:{{ $soGoi }}" class="nx-staff__link">
                                    @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 14])
                                    {{ $nv->phone }}
                                </a>
                            @endif

                            @if($soZalo)
                                <a href="https://zalo.me/{{ $soZalo }}" class="nx-staff__link"
                                   target="_blank" rel="noopener nofollow">Zalo</a>
                            @endif

                            @if($nv->public_email)
                                <a href="mailto:{{ $nv->public_email }}" class="nx-staff__link">
                                    {{ $nv->public_email }}
                                </a>
                            @endif
                        </div>

                        {{-- Nut mo popup xin tu van. Popup do component
                             advisor-modal lo, chi in mot lan cho ca trang. --}}
                        <button type="button" class="nx-btn nx-btn--sm nx-staff__nut"
                                data-nx-lien-he="{{ $nv->id }}" data-nx-ten="{{ $nv->name }}">
                            @include('frontend.noxh.component.icon', ['name' => 'send', 'size' => 14])
                            Liên hệ
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
