{{--
    Dai anh xanh o dau hai trang tin tuc (ban ve noxh_image/tin-tuc-fix.webp).

    Anh nen lay tu o "Dải ảnh đầu các trang trong" trong man hinh Giới thiệu -
    cung mot o ma trang chi tiet du an dung, nen doi anh mot lan la ca hai
    trang doi theo.

    $theTieuDe = 'h1' o trang danh muc (do la tieu de chinh cua trang), 'p' o trang
    chi tiet (tieu de chinh o do la ten bai viet).
--}}
@php
    $nenDai = trim((string) ($intro['pagehead_image'] ?? ''));
    $theTieuDe = ($theTieuDe ?? 'h1') === 'h1' ? 'h1' : 'p';
    $tenDai = $intro['news_heading'] ?? 'Tin tức';
    $moTaDai = trim((string) ($intro['news_description'] ?? ''));
@endphp

<section class="nx-tin-dai{{ $nenDai !== '' ? ' co-nen' : '' }}"
         @if($nenDai !== '') style="--nx-nen: url('{{ e($nenDai) }}')" @endif>
    <div class="nx__container">
        <{{ $theTieuDe }} class="nx-tin-dai__ten">{{ $tenDai }}</{{ $theTieuDe }}>

        @if($moTaDai !== '')
            <p class="nx-tin-dai__mo-ta">{{ $moTaDai }}</p>
        @endif
    </div>
</section>
