{{--
    Thanh tab dinh o dau trang chi tiet du an.

    Chi liet ke nhung khoi THUC SU co du lieu: du an chua nhap tien do thi
    khong co tab "Tien do" dan xuong mot cho trong. Nhan tren tab do quan tri
    dat trong Cau hinh chung.

    Tham so: $tab (mang ['id', 'ten', 'icon'])
--}}
@if(count($tab) > 1)
    <nav class="nx-pd-tab" aria-label="Các phần của trang dự án">
        <div class="nx__container nx-pd-tab__trong">
            @foreach($tab as $t)
                <a href="#{{ $t['id'] }}" class="nx-pd-tab__muc" data-nx-tab="{{ $t['id'] }}">
                    @include('frontend.noxh.component.icon', ['name' => $t['icon'], 'size' => 18])
                    <span>{{ $t['ten'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
@endif
