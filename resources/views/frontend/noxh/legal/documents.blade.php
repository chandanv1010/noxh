@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Pháp lý NOXH' => url('/phap-ly-noxh'), 'Văn bản pháp luật' => ''],
    'tieuDe' => 'Văn bản pháp luật về nhà ở xã hội',
    'moTa' => 'Luật, nghị định, thông tư và công văn hướng dẫn mới nhất, sắp xếp theo ngày có hiệu lực.',
])

<div class="nx-listing nx-listing--left">
    <aside>
        <div class="nx-panel">
            <h2 class="nx-panel__title">Loại văn bản</h2>
            <div class="nx-filter__group">
                <a href="{{ url('/phap-ly-noxh/van-ban') }}" class="nx-filter__option" style="text-decoration:none">
                    <span>Tất cả</span>
                    <span>{{ array_sum($demTheoLoai) }}</span>
                </a>
                @foreach($loai as $ma => $ten)
                    <a href="{{ url('/phap-ly-noxh/van-ban?loai=' . $ma) }}" class="nx-filter__option"
                       style="text-decoration:none;{{ $loaiChon === $ma ? 'color:#1668e3;font-weight:600' : '' }}">
                        <span>{{ $ten }}</span>
                        <span>{{ $demTheoLoai[$ma] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        @include('frontend.noxh.component.expert-box')
    </aside>

    <div>
        <form method="GET" class="nx-searchbar">
            @if($loaiChon)<input type="hidden" name="loai" value="{{ $loaiChon }}">@endif
            <input type="text" name="tu-khoa" value="{{ request('tu-khoa') }}" placeholder="Tìm theo tên hoặc số hiệu văn bản">
            <button type="submit" class="nx-btn">Tìm kiếm</button>
        </form>

        <div class="nx-panel">
            @forelse($vanBan as $vb)
                <div class="nx-legal-item">
                    <span class="nx-legal-item__icon">
                        @include('frontend.noxh.component.icon', ['name' => 'file-text', 'size' => 24])
                    </span>
                    <div class="nx-legal-item__body">
                        <strong>{{ $vb->title }}</strong>
                        @if($vb->doc_number || $vb->issuer)
                            <p>{{ trim($vb->doc_number . ($vb->issuer ? ' · ' . $vb->issuer : ''), ' ·') }}</p>
                        @endif
                        @if($vb->summary)
                            <p>{{ \Illuminate\Support\Str::words(strip_tags($vb->summary), 30, '…') }}</p>
                        @endif
                        <time>
                            @php
                                $moc = [];
                                if ($vb->issued_date) { $moc[] = 'Ban hành ' . $vb->issued_date->format('d/m/Y'); }
                                if ($vb->effective_date) { $moc[] = 'Hiệu lực từ ' . $vb->effective_date->format('d/m/Y'); }
                                // Nguoi dung can biet file nang bao nhieu va dinh dang gi
                                // TRUOC khi bam tai - nhat la khi dung 3G.
                                if ($vb->file_type) { $moc[] = strtoupper($vb->file_type); }
                                if ($vb->file_size) { $moc[] = dung_luong($vb->file_size); }
                                if ($vb->download_count) { $moc[] = number_format($vb->download_count, 0, ',', '.') . ' lượt tải'; }
                            @endphp
                            {{ implode(' · ', $moc) }}
                        </time>
                    </div>
                    @if($vb->file)
                        <a href="{{ route('noxh.legal.download', $vb->id) }}" class="nx-btn nx-btn--ghost nx-btn--sm">
                            @include('frontend.noxh.component.icon', ['name' => 'download', 'size' => 15])
                            Tải về
                        </a>
                    @endif
                </div>
            @empty
                <div class="nx-empty">Chưa có văn bản nào phù hợp.</div>
            @endforelse
        </div>

        @include('frontend.noxh.component.pagination', ['model' => $vanBan])
    </div>
</div>
@endsection
