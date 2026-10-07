{{--
    Anh lon + dai anh nho o dau trang chi tiet du an.

    Bam vao mot o anh nho thi doi anh lon (JS o script.blade.php); khong co JS
    thi moi o van la mot the <a> mo thang anh do, nen trang khong chet.

    Bam vao anh LON (hoac nut phong to) thi mo lightbox; bam vao anh nho thi chi
    doi anh lon - hai viec khac nhau nen khong the gop lam mot.

    O anh nho CUOI cung mang chu "+ N anh" khi album con du ra - dung dem the
    la lech, phai tinh tu tong so anh.

    Tham so: $duAn, $anh (['chinh' => ..., 'phu' => [...]]), $intro
--}}
@php
    $chinh = nx_anh($anh['chinh'] ?? '', 'du-an');
    $phu = $anh['phu'] ?? [];

    // Ban thiet ke ve dung 5 o anh nho.
    $soO = 5;
    $nho = array_slice($phu, 0, $soO);
    $con = max(0, count($phu) - $soO);

    $video = trim((string) ($duAn->video_url ?? ''));
    $chuVideo = $intro['projectdetail_video_text'] ?? 'Xem video dự án';
    $chuThem = $intro['projectdetail_gallery_more'] ?? '+ {so} ảnh';

    // Moi du an mot nhom rieng: lightbox gom anh cua CA du an (anh lon, dai anh
    // nho, va album o tab Hinh anh) de bam mui ten la di het bo anh, khong bi
    // nhot trong mot khoi.
    $nhomAnh = 'du-an-' . ($duAn->id ?? 0);
@endphp

<div class="nx-pd-anh">
    <figure class="nx-pd-anh__chinh">
        <img id="nx-pd-anh-chinh" src="{{ $chinh }}" alt="{{ $duAn->name }}"
             fetchpriority="high" decoding="async">

        @if($video !== '')
            <button type="button" class="nx-pd-anh__video"
                    data-nx-video="{{ e($video) }}"
                    aria-label="{{ $chuVideo }}">
                <span class="nx-pd-anh__play">
                    @include('frontend.noxh.component.icon', ['name' => 'play', 'size' => 22])
                </span>
                {{ $chuVideo }}
            </button>
        @endif

        <button type="button" class="nx-pd-anh__to nx-lb__goi"
                data-nx-lb="{{ e($chinh) }}" data-nx-nhom="{{ $nhomAnh }}"
                aria-label="Phóng to ảnh">
            @include('frontend.noxh.component.icon', ['name' => 'search', 'size' => 20])
        </button>
    </figure>

    @if(count($nho))
        <div class="nx-pd-anh__nho">
            @foreach($nho as $i => $a)
                @php $cuoi = $con > 0 && $i === count($nho) - 1; @endphp
                {{-- KHONG gan data-nx-lb o day. Dai anh nho nay co nhiem vu KHAC:
                     bam vao thi doi anh lon dang xem. Neu vua doi anh lon vua mo
                     lightbox thi hai viec chong nhau, nhin rat roi.
                     Muon xem anh lon thi bam vao anh lon (nut phong to) hoac vao
                     album o tab "Hinh anh". --}}
                <a href="{{ $a }}" class="nx-pd-anh__o{{ $cuoi ? ' co-them' : '' }}"
                   data-nx-anh="{{ e($a) }}">
                    {{-- Khong dat loading="lazy": dai anh nay nam ngay tren
                         man hinh dau, de lazy thi trinh duyet bo qua va nguoi
                         dung nhin thay nam o xam. --}}
                    <img src="{{ $a }}" alt="{{ $duAn->name }}" decoding="async">
                    @if($cuoi)
                        <span>{{ strtr($chuThem, ['{so}' => $con]) }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif
</div>
