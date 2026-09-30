{{--
    Cot phai cua trang ket qua.

    Ba ban ve khac nhau o day nhieu nhat: ban thanh cong co mot the "Co hoi
    an cu dang trong tam tay" kem tranh; ban luu y va ban that bai co tranh
    kem bong thoai, roi mot the danh sach va mot the goi y. Cau truc chung
    nhau: TRANH + vai THE, moi the co tieu de, doan chu, danh sach y - nen
    doc het tu bang introduces theo muc ket qua.
--}}
@php
    $mau = ['high' => 'green', 'medium' => 'amber', 'low' => 'rose'][$muc] ?? 'sky';
    [$nen, $net] = \App\Classes\NoxhTone::mau($mau);

    // Ban thanh cong ve the TRUOC roi moi toi tranh; hai ban kia nguoc lai.
    $tranhTruoc = $muc !== 'high';

    $tranh = $o('side_image');

    $the = [];
    foreach ([1, 2, 3] as $i) {
        $dau = $o('side' . $i . '_heading');
        $chu = $o('side' . $i . '_body');
        $y = array_values(array_filter(array_map('trim',
            preg_split('/\r\n|\r|\n/', (string) $o('side' . $i . '_lines'))
        )));

        if ($dau !== '' || $chu !== '' || count($y)) {
            $the[] = [
                'dau' => $dau,
                'chu' => $chu,
                'y' => $y,
                'hinh' => $o('side' . $i . '_icon'),
                'mau' => $o('side' . $i . '_tone', $mau),
            ];
        }
    }
@endphp

@if($tranhTruoc && $tranh)
    <img class="nx-kq-phai__tranh" src="{{ $tranh }}" alt="" loading="lazy">
@endif

@foreach($the as $t)
    @php [$tNen, $tNet] = \App\Classes\NoxhTone::mau($t['mau']); @endphp

    <div class="nx-kq-the" style="--nx-nen: {{ $tNen }}; --nx-net: {{ $tNet }}">
        @if($t['dau'] !== '')
            <h3>
                @if($t['hinh'] && \App\Classes\NoxhIcon::hopLe($t['hinh']))
                    <span class="nx-kq-the__hinh">
                        @include('frontend.noxh.component.icon', ['name' => $t['hinh'], 'size' => 20])
                    </span>
                @endif
                {{ $t['dau'] }}
            </h3>
        @endif

        @if($t['chu'] !== '')
            @foreach(preg_split('/\r\n\r\n|\n\n/', $t['chu']) as $doan)
                @if(trim($doan) !== '')
                    <p>{{ trim($doan) }}</p>
                @endif
            @endforeach
        @endif

        @if(count($t['y']))
            <ul>
                @foreach($t['y'] as $dong)
                    <li>
                        @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17])
                        <span>{{ $dong }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if(!$tranhTruoc && $tranh && $loop->first)
            <img class="nx-kq-the__tranh" src="{{ $tranh }}" alt="" loading="lazy">
        @endif
    </div>
@endforeach

@if(!$tranhTruoc && $tranh && !count($the))
    <img class="nx-kq-phai__tranh" src="{{ $tranh }}" alt="" loading="lazy">
@endif
