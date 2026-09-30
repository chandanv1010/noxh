{{--
    Cot phai cua buoc wizard (ban ve w-3, w-4, w-5).

    Tam nao chi co tranh thi in moi tranh; tam co tieu de thi ve khung mau
    kem danh sach dau tich. Tat ca deu la du lieu - man hinh quan tri
    "Tam cot phai".
--}}
@foreach($cau->panels as $tam)
    @php
        [$nen, $net] = $tam->mauHinh();
        $anh = trim((string) $tam->image);
        $hinh = \App\Classes\NoxhIcon::hopLe($tam->icon) ? $tam->icon : '';
        $chu = trim((string) $tam->body);
        $y = $tam->dongY();
        $dau = trim((string) $tam->heading);
        $chiTranh = $dau === '' && $chu === '' && !count($y);
    @endphp

    @if($chiTranh)
        @if($anh !== '')
            <img class="nx-wz-phai__tranh" src="{{ $anh }}" alt="" loading="lazy">
        @endif
    @else
        <div class="nx-wz-tam-phai" style="--nx-nen: {{ $nen }}; --nx-net: {{ $net }}">
            @if($dau !== '')
                <h3>
                    @if($hinh)
                        <span class="nx-wz-tam-phai__hinh">
                            @include('frontend.noxh.component.icon', ['name' => $hinh, 'size' => 20])
                        </span>
                    @endif
                    {{ $dau }}
                </h3>
            @endif

            @if($chu !== '')
                @foreach(preg_split('/\r\n\r\n|\n\n/', $chu) as $doan)
                    @if(trim($doan) !== '')
                        <p>{{ trim($doan) }}</p>
                    @endif
                @endforeach
            @endif

            @if(count($y))
                <ul>
                    @foreach($y as $dong)
                        <li>
                            @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17])
                            <span>{{ $dong }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if($anh !== '')
                <img src="{{ $anh }}" alt="" loading="lazy">
            @endif
        </div>
    @endif
@endforeach
