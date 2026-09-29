{{--
    Danh sach giay to - dung cho ca tab "Phap ly" lan tab "Tai lieu".

    Hai tab do cung doc bang project_documents, khac nhau o cot group, nen
    chi can mot khuon ve.

    Tham so: $giayTo (collection ProjectDocument)
--}}
<div class="nx-doc-grid">
    @foreach($giayTo as $hs)
        <a href="{{ $hs->file ?: '#' }}" class="nx-doc"
           @if($hs->file) target="_blank" rel="noopener" @endif>
            <span class="nx-doc__icon">
                @include('frontend.noxh.component.icon', ['name' => 'tab-doc', 'size' => 22])
            </span>
            <span>
                <strong>{{ $hs->title }}</strong>
                @if($hs->doc_number)<span>Số: {{ $hs->doc_number }}</span>@endif
                @if($hs->issued_date)<span>Ngày: {{ \Illuminate\Support\Carbon::parse($hs->issued_date)->format('d/m/Y') }}</span>@endif
                @if($hs->issuer)<span>{{ $hs->issuer }}</span>@endif
            </span>
        </a>
    @endforeach
</div>
