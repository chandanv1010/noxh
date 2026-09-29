{{--
    Mot dong van ban phap luat o cot phai trang Phong phap ly.

    Hinh ben trai la mot TO GIAY GAP GOC mang mau theo dinh dang file (PDF do,
    DOC xanh...) dung nhu ban ve - mau lay tu file_type chu khong phai chon
    tay, nen quan tri up file .doc la doi mau theo.

    Tham so: $vb (LegalDocument), $intro
--}}
@php
    $loai = mb_strtolower(trim((string) ($vb->file_type
        ?: ($vb->file ? pathinfo($vb->file, PATHINFO_EXTENSION) : ''))));

    // Ma nhom vua quyet dinh mau the vua la chu in tren the: "docx" dai qua
    // khong vua o 33px, ban ve cung chi viet DOC.
    $nhomMau = [
        'pdf' => 'pdf',
        'doc' => 'doc', 'docx' => 'doc',
        'xls' => 'xls', 'xlsx' => 'xls', 'csv' => 'xls',
    ];

    $nhom = $nhomMau[$loai] ?? 'khac';

    $ngay = $vb->effective_date ?: $vb->issued_date;
@endphp

<div class="nx-vb">
    <span class="nx-vb__icon nx-vb__icon--{{ $nhom }}">
        @include('frontend.noxh.component.icon', ['name' => 'doc-line', 'size' => 16])
        @if($loai !== '')<i>{{ mb_strtoupper($nhom === 'khac' ? mb_substr($loai, 0, 4) : $nhom) }}</i>@endif
    </span>

    <div class="nx-vb__chu">
        <strong>{{ $vb->title }}</strong>
        @if($vb->summary)
            <p>{{ \Illuminate\Support\Str::words(strip_tags($vb->summary), 18, '…') }}</p>
        @endif

        {{-- Chua nhap tom tat thi dong ngay thay luon cho tom tat, co them
             chu "Co hieu luc tu" cho ro - dung nhu ban ve. --}}
        @if($ngay)
            <time datetime="{{ $ngay->format('Y-m-d') }}"
                  class="{{ $vb->summary ? '' : 'la-mo-ta' }}">
                @if($vb->summary)
                    {{ $ngay->format('d/m/Y') }}
                @else
                    {{ strtr($intro['legal_doc_effective_text'] ?? 'Có hiệu lực từ {ngay}', ['{ngay}' => $ngay->format('d/m/Y')]) }}
                @endif
            </time>
        @endif
    </div>

    @if($vb->file)
        <a href="{{ route('noxh.legal.download', $vb->id) }}" class="nx-vb__tai"
           title="{{ $intro['legal_doc_download_title'] ?? 'Tải văn bản về' }}">
            @include('frontend.noxh.component.icon', ['name' => 'download', 'size' => 19])
        </a>
    @endif
</div>
