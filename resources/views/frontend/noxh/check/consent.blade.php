@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/start-fix.jpg (do 1:1, moi con so chep thang tu ban ve).
    //
    // Khong chuoi nao viet cung o day - moi dong chu, moi hinh deu doc tu
    // nhom "Kiểm tra điều kiện - trang mở đầu" cua man hinh Gioi thieu.
    $nen = trim((string) ($intro['start_bg'] ?? ''));
    $anhDau = trim((string) ($intro['start_image'] ?? ''));
    $anhKhien = trim((string) ($intro['start_privacy_image'] ?? ''));

    $moTa = trim((string) ($intro['start_description'] ?? ''));
    $baoMat = trim((string) ($intro['start_privacy_text'] ?? ''));
    $luuY = trim((string) ($intro['start_disclaimer'] ?? ''));

    $duongDongY = trim((string) ($intro['start_agree_link'] ?? '')) ?: '/chinh-sach-bao-mat';
@endphp

@section('content')
<div class="nx-kt" @if($nen) style="--nx-nen: url('{{ $nen }}')" @endif>
    <div class="nx-kt__trong">
        @if($anhDau)
            <img class="nx-kt__hinh" src="{{ $anhDau }}" alt="" width="122" height="122">
        @endif

        <h1 class="nx-kt__ten">
            {{ $intro['start_heading'] ?? 'Kiểm tra điều kiện mua' }}
            @if(!empty($intro['start_heading_blue']))
                <span>{{ $intro['start_heading_blue'] }}</span>
            @endif
        </h1>

        <span class="nx-kt__gach" aria-hidden="true"></span>

        @if($moTa !== '')
            <p class="nx-kt__mo">{{ $moTa }}</p>
        @endif

        @if(!empty($intro['start_time_text']) || !empty($intro['start_time_strong']))
            <p class="nx-kt-gio">
                @if(!empty($intro['start_time_icon']))
                    @include('frontend.noxh.component.icon', ['name' => $intro['start_time_icon'], 'size' => 22])
                @endif
                {{ $intro['start_time_text'] ?? '' }}
                @if(!empty($intro['start_time_strong']))
                    <strong>{{ $intro['start_time_strong'] }}</strong>
                @endif
            </p>
        @endif

        {{-- Nut bat dau bi khoa cho toi khi nguoi dung tich dong y - JS chi de
             tien tay, phia may chu van luu consent = true khi nhan bai. --}}
        <form method="GET" action="{{ route('noxh.check.form') }}">
            <div class="nx-kt-bm">
                @if($anhKhien)
                    <img class="nx-kt-bm__hinh" src="{{ $anhKhien }}" alt="" width="128" height="128">
                @endif

                <div class="nx-kt-bm__chu">
                    @if(!empty($intro['start_privacy_heading']))
                        <h2>{{ $intro['start_privacy_heading'] }}</h2>
                    @endif

                    @if($baoMat !== '')
                        <p>
                            @if(!empty($intro['start_privacy_icon']))
                                @include('frontend.noxh.component.icon', ['name' => $intro['start_privacy_icon'], 'size' => 18])
                            @endif
                            {{ $baoMat }}
                        </p>
                    @endif
                </div>

                <label class="nx-kt-bm__y">
                    <input type="checkbox" id="nx-dong-y">
                    <span>
                        {{ $intro['start_agree_text'] ?? 'Tôi đã đọc và đồng ý với' }}
                        <a href="{{ url($duongDongY) }}">
                            {{ $intro['start_agree_link_text'] ?? 'chính sách bảo mật thông tin' }}
                        </a>
                    </span>
                </label>
            </div>

            <button type="submit" class="nx-kt-nut" id="nx-bat-dau" disabled>
                {{ $intro['start_button'] ?? 'Bắt đầu kiểm tra' }}
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 26])
            </button>
        </form>

        @if(!empty($intro['start_note']))
            <p class="nx-kt__ghi">
                @if(!empty($intro['start_note_icon']))
                    @include('frontend.noxh.component.icon', ['name' => $intro['start_note_icon'], 'size' => 17])
                @endif
                {{ $intro['start_note'] }}
            </p>
        @endif

        @if($luuY !== '')
            <p class="nx-kt-luu">
                @if(!empty($intro['start_disclaimer_icon']))
                    @include('frontend.noxh.component.icon', ['name' => $intro['start_disclaimer_icon'], 'size' => 26])
                @endif
                <span>{{ $luuY }}</span>
            </p>
        @endif
    </div>
</div>

@push('script')
<script>
    (function () {
        var tich = document.getElementById('nx-dong-y');
        var nut = document.getElementById('nx-bat-dau');
        if (!tich || !nut) return;
        tich.addEventListener('change', function () { nut.disabled = !tich.checked; });
    })();
</script>
@endpush
@endsection
