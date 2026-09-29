{{--
    Thanh tab cua trang chi tiet du an.

    Bam mot tab thi DOI NOI DUNG trong khung ben duoi chu khong truot xuong -
    dung nhu ban ve. Khong co JS thi moi tab van la mot lien ket neo den dung
    khoi do, va luc do cac khoi deu hien ra nen khong ai bi ket.

    Chi liet ke nhung khoi THUC SU co du lieu: du an chua nhap tien do thi
    khong co tab "Tien do" dan sang mot khung trong.

    Tham so: $tab (mang ['ma', 'ten', 'icon']), $tabDau
--}}
@if(count($tab) > 1)
    <nav class="nx-pd-tab" aria-label="Các phần của trang dự án">
        <div class="nx__container nx-pd-tab__trong" role="tablist">
            @foreach($tab as $t)
                <a href="#tab-{{ $t['ma'] }}"
                   class="nx-pd-tab__muc{{ $t['ma'] === $tabDau ? ' is-chon' : '' }}"
                   role="tab" aria-controls="tab-{{ $t['ma'] }}"
                   aria-selected="{{ $t['ma'] === $tabDau ? 'true' : 'false' }}"
                   data-nx-tab="{{ $t['ma'] }}">
                    @include('frontend.noxh.component.icon', ['name' => $t['icon'], 'size' => 19])
                    <span>{{ $t['ten'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>
@endif
