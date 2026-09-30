@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/w-1.jpg (buoc 1) va w-2..w-5 (cac buoc sau).
    //
    // Buoc dau co dai gioi thieu va thanh buoc nam ngoai the; tu buoc hai tro
    // di bo dai di, thanh buoc thanh mot the rieng o tren, duoi la mot hang
    // hai cot (noi dung + cot phai) - dung nhu ban ve.
    //
    // Khong chuoi nao viet cung: cau hoi, dap an, tinh huong, tam cot phai,
    // hinh va ten tung buoc nam trong CSDL; cac dong chu khung nam o nhom
    // "Kiểm tra điều kiện - khung trang" cua man hinh Gioi thieu.
    $tong = $cauHoi->count();
    $dauTien = $buoc === 1;
    $nhapTin = $cau->laBuocNhapTin();
    $coPhai = $cau->panels->count() > 0;

    $nenDai = trim((string) ($intro['wizard_image'] ?? ''));

    $the = [];
    foreach ([1, 2, 3, 4] as $i) {
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

    // Nut lui tro ve buoc TRUOC TREN DUONG DI, khong phai buoc lien truoc:
    // co buoc bi bo qua thi buoc lien truoc khong ai di qua ca.
    $viTri = array_search($buoc, $duongDi, true);
    $buocTruoc = $viTri > 0 ? $duongDi[$viTri - 1] : null;
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
        @unless($dauTien)
            <div class="nx-panel nx-wz-buoc-the">
                <div class="nx-wz-the__dinh">
                    @if($buocTruoc)
                        <a href="{{ route('noxh.check.form', $buocTruoc) }}" class="nx-wz-the__lui">
                            @include('frontend.noxh.component.icon', ['name' => 'arrow-left', 'size' => 18])
                            {{ $chuLui }}
                        </a>
                    @else
                        <span></span>
                    @endif
                    <span class="nx-wz-the__dem">{{ $dongSo }}</span>
                </div>

                <div class="nx-wz-buoc nx-wz-buoc--trong">
                    @include('frontend.noxh.check.steps', ['trong' => true])
                </div>
            </div>
        @endunless

        <div class="nx-wz__khung {{ $coPhai ? 'co-phai' : '' }}">
            <form method="POST" action="{{ $noiGui }}" class="nx-panel nx-wz-the {{ $dauTien ? '' : 'la-trong' }}">
                @csrf

                @include('frontend.noxh.component.alert')

                @if($dauTien)
                    <p class="nx-wz-the__so">{{ $dongSo }}</p>
                    <h2 class="nx-wz-the__hoi">{{ $cau->question }}</h2>

                    @if($nhac !== '')
                        <p class="nx-wz-the__nhac">{{ $nhac }}</p>
                    @endif
                @else
                    <div class="nx-wz-the__giua
                        {{ $cau->layout === 'card' || $nhapTin ? 'nam-ngang' : '' }}
                        {{ $cau->layout === 'list' ? 'canh-trai' : '' }}">
                        @if($anhCau !== '')
                            <img class="nx-wz-the__anh" src="{{ $anhCau }}" alt="" width="116" height="116">
                        @elseif($hinhCau)
                            <span class="nx-wz-the__hinh" style="--nx-nen: {{ $nenCau }}; --nx-net: {{ $netCau }}">
                                @include('frontend.noxh.component.icon', ['name' => $hinhCau, 'size' => 52])
                            </span>
                        @endif

                        <div class="nx-wz-the__loi">
                            <h2 class="nx-wz-the__hoi">{{ $cau->question }}</h2>

                            @if($nhac !== '')
                                <p class="nx-wz-the__nhac">{{ $nhac }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($nhapTin)
                    @include('frontend.noxh.check.contact')
                @elseif($cau->laMatrix())
                    @include('frontend.noxh.check.matrix')
                @elseif($cau->layout === 'list')
                    @include('frontend.noxh.check.list')
                @elseif($cau->layout === 'card')
                    @include('frontend.noxh.check.card')
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

            @if($coPhai)
                <aside class="nx-wz-phai">
                    @include('frontend.noxh.check.panels')
                </aside>
            @endif
        </div>
    </div>
</div>
@endsection
