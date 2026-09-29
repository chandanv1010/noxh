{{--
    Danh sach giay to - dung cho ca tab "Phap ly" lan tab "Tai lieu".

    Hai tab do cung doc bang project_documents, khac nhau o cot group, nen
    chi can mot khuon ve.

    Co file thi the la mot lien ket MO SANG TAB MOI, kem duoi hinh dinh dang
    file. Chua co file thi ve mot o chu thuong chu khong phai lien ket tro
    vao "#": mot the bam duoc ma khong dan di dau la kho chiu hon la mot the
    nhin ro la chua co gi.

    Tham so: $giayTo (collection ProjectDocument), $intro
--}}
<div class="nx-doc-grid">
    @foreach($giayTo as $hs)
        @php
            $duong = trim((string) $hs->file);
            $loai = strtoupper((string) ($hs->file_type
                ?: ($duong !== '' ? pathinfo($duong, PATHINFO_EXTENSION) : '')));
        @endphp

        <{{ $duong !== '' ? 'a' : 'div' }} class="nx-doc{{ $duong === '' ? ' la-trong' : '' }}"
            @if($duong !== '') href="{{ $duong }}" target="_blank" rel="noopener" @endif>

            <span class="nx-doc__icon">
                @include('frontend.noxh.component.icon', ['name' => 'tab-doc', 'size' => 22])
                @if($loai !== '')<i>{{ $loai }}</i>@endif
            </span>

            <span class="nx-doc__chu">
                <strong>{{ $hs->title }}</strong>
                @if($hs->doc_number)<span>Số: {{ $hs->doc_number }}</span>@endif
                @if($hs->issued_date)<span>Ngày: {{ \Illuminate\Support\Carbon::parse($hs->issued_date)->format('d/m/Y') }}</span>@endif
                @if($hs->issuer)<span>{{ $hs->issuer }}</span>@endif
                @if($duong === '')
                    <span class="nx-doc__chua">{{ $intro['projectdetail_doc_empty_file'] ?? 'Chưa đính kèm file' }}</span>
                @endif
            </span>

            @if($duong !== '')
                <span class="nx-doc__mo" aria-hidden="true">
                    @include('frontend.noxh.component.icon', ['name' => 'eye', 'size' => 18])
                </span>
            @endif
        </{{ $duong !== '' ? 'a' : 'div' }}>
    @endforeach
</div>
