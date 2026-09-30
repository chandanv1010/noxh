@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/w-1.jpg (buoc 1) va noxh_image/w-2.jpg (cac buoc sau).
    //
    // Hai ban ve ve KHAC nhau co y: buoc dau co dai gioi thieu va thanh buoc
    // nam ngoai the, cac buoc sau bo dai di, dua thanh buoc vao trong the va
    // them hang "Quay lai / Cau n/8" o dinh the. O day theo dung nhu vay.
    //
    // Khong chuoi nao viet cung: cau hoi, dap an, tinh huong, hinh va ten
    // tung buoc nam trong CSDL; cac dong chu khung nam o nhom "Kiểm tra điều
    // kiện - khung trang" cua man hinh Gioi thieu.
    $tong = $cauHoi->count();
    $dauTien = $buoc === 1;

    $nenDai = trim((string) ($intro['wizard_image'] ?? ''));

    $the = [];
    foreach ([1, 2, 3] as $i) {
        $chu = trim((string) ($intro['wizard_chip' . $i . '_text'] ?? ''));
        if ($chu !== '') {
            $the[] = ['chu' => $chu, 'hinh' => trim((string) ($intro['wizard_chip' . $i . '_icon'] ?? ''))];
        }
    }

    $dongSo = strtr(
        (string) ($intro['wizard_step_text'] ?? 'Câu {so}/{tong}'),
        ['{so}' => $buoc, '{tong}' => $tong]
    );

    $moTa = strtr((string) ($intro['wizard_subtitle'] ?? ''), ['{so}' => $tong]);

    $ghiChu = trim((string) $cau->foot_note);
    $ghiChuPhu = trim((string) $cau->foot_note_sub);
    $nhac = trim((string) $cau->hint);

    $anhCau = trim((string) $cau->image);
    $hinhCau = \App\Classes\NoxhIcon::hopLe($cau->icon) ? $cau->icon : '';
    [$nenCau, $netCau] = \App\Classes\NoxhTone::mau($cau->icon_tone);

    $chuLui = $intro['wizard_back_text'] ?? 'Quay lại';

    // Buoc cuoi gui thang sang trang cham diem, cac buoc con lai chi ghi cau
    // tra loi vao phien roi sang buoc ke tiep.
    $noiGui = $cuoiCung ? route('noxh.check.submit') : route('noxh.check.step', $buoc);
@endphp

@section('content')
<div class="nx-wz {{ $dauTien ? '' : 'nx-wz--trong' }}">
    @if($dauTien)
        <div class="nx-wz-dai" @if($nenDai) style="--nx-anh: url('{{ $nenDai }}')" @endif>
            <div class="nx__container nx-wz-dai__trong">
                <div class="nx-wz-dai__chu">
                    <h1 class="nx-wz-dai__ten">
                        {{ $intro['wizard_heading'] ?? 'Kiểm tra khả năng mua' }}
                        @if(!empty($intro['wizard_heading_blue']))
                            <span>{{ $intro['wizard_heading_blue'] }}</span>
                        @endif
                    </h1>

                    @if($moTa !== '')
                        <p class="nx-wz-dai__mo">{{ $moTa }}</p>
                    @endif

                    @if(count($the))
                        <ul class="nx-wz-chip">
                            @foreach($the as $t)
                                <li>
                                    @if($t['hinh'])
                                        <span class="nx-wz-chip__hinh">
                                            @include('frontend.noxh.component.icon', ['name' => $t['hinh'], 'size' => 15])
                                        </span>
                                    @endif
                                    {{ $t['chu'] }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="nx-wz-buoc">
            @include('frontend.noxh.check.steps', ['trong' => false])
        </div>
    @endif

    <div class="nx__container">
        <form method="POST" action="{{ $noiGui }}" class="nx-panel nx-wz-the {{ $dauTien ? '' : 'la-trong' }}">
            @csrf

            @unless($dauTien)
                <div class="nx-wz-the__dinh">
                    <a href="{{ route('noxh.check.form', $buoc - 1) }}" class="nx-wz-the__lui">
                        @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 18])
                        {{ $chuLui }}
                    </a>
                    <span class="nx-wz-the__dem">{{ $dongSo }}</span>
                </div>

                <div class="nx-wz-buoc nx-wz-buoc--trong">
                    @include('frontend.noxh.check.steps', ['trong' => true])
                </div>
            @endunless

            @include('frontend.noxh.component.alert')

            @if($dauTien)
                <p class="nx-wz-the__so">{{ $dongSo }}</p>
                <h2 class="nx-wz-the__hoi">{{ $cau->question }}</h2>

                @if($nhac !== '')
                    <p class="nx-wz-the__nhac">{{ $nhac }}</p>
                @endif
            @else
                <div class="nx-wz-the__giua">
                    @if($anhCau !== '')
                        <img class="nx-wz-the__anh" src="{{ $anhCau }}" alt="" width="116" height="116">
                    @elseif($hinhCau)
                        <span class="nx-wz-the__hinh" style="--nx-nen: {{ $nenCau }}; --nx-net: {{ $netCau }}">
                            @include('frontend.noxh.component.icon', ['name' => $hinhCau, 'size' => 52])
                        </span>
                    @endif

                    <h2 class="nx-wz-the__hoi">{{ $cau->question }}</h2>

                    @if($nhac !== '')
                        <p class="nx-wz-the__nhac">{{ $nhac }}</p>
                    @endif
                </div>
            @endif

            @if($cau->laMatrix())
                @include('frontend.noxh.check.matrix')
            @elseif($cau->options->count())
                <div class="nx-wz-luoi">
                    @foreach($cau->options as $da)
                        @include('frontend.noxh.check.option', ['da' => $da, 'daChon' => $daChon])
                    @endforeach
                </div>
            @else
                {{-- Cau chua nhap dap an nao thi cho go tu do, van ghi nhan
                     duoc cau tra loi thay vi bo trong mot buoc. --}}
                <input type="text" name="traLoi" class="nx-wz-go"
                       value="{{ old('traLoi', $daChon) }}"
                       placeholder="{{ $cau->criteria_label }}">
            @endif

            @if($ghiChu !== '')
                <p class="nx-wz-ghi {{ $ghiChuPhu !== '' ? 'co-phu' : '' }}">
                    <span class="nx-wz-ghi__hinh">
                        @include('frontend.noxh.component.icon', ['name' => 'info', 'size' => 15])
                    </span>
                    <span>
                        <strong>{{ $ghiChu }}</strong>
                        @if($ghiChuPhu !== '')
                            <small>{{ $ghiChuPhu }}</small>
                        @endif
                    </span>
                </p>
            @endif

            @if($cuoiCung)
                <div class="nx-wz-nhan">
                    <h3>{{ $intro['wizard_contact_heading'] ?? 'Nhận kết quả' }}</h3>

                    @if(!empty($intro['wizard_contact_description']))
                        <p>{{ $intro['wizard_contact_description'] }}</p>
                    @endif

                    <div class="nx-wz-nhan__o">
                        <div class="nx-field">
                            <label for="nx-wz-ten">{{ $intro['wizard_contact_name'] ?? 'Họ và tên' }} <i>*</i></label>
                            <input type="text" id="nx-wz-ten" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="nx-field">
                            <label for="nx-wz-sdt">{{ $intro['wizard_contact_phone'] ?? 'Số điện thoại' }} <i>*</i></label>
                            <input type="tel" id="nx-wz-sdt" name="phone" value="{{ old('phone') }}" required>
                        </div>
                    </div>

                    @if(!empty($intro['wizard_contact_note']))
                        <p class="nx-wz-nhan__cam">{{ $intro['wizard_contact_note'] }}</p>
                    @endif
                </div>
            @endif

            <div class="nx-wz-nut">
                {{-- Nut lui dung chung mot form nhung gui sang duong dan khac
                     va KHONG bat dien: nguoi dung dang di nguoc, bat ho chon
                     dap an truoc khi quay lai la vo ly. --}}
                <button type="submit" name="huong" value="lui" formnovalidate
                        class="nx-wz-nut__lui"
                        formaction="{{ route('noxh.check.step', $buoc) }}">
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 17])
                    {{ $chuLui }}
                </button>

                <button type="submit" name="huong" value="toi" class="nx-wz-nut__toi">
                    @if($cuoiCung)
                        {{ $intro['wizard_finish_text'] ?? 'Xem kết quả' }}
                    @else
                        {{ $intro['wizard_next_text'] ?? 'Tiếp theo' }}
                    @endif
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 17])
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
