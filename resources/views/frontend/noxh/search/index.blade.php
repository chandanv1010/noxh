@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Tìm kiếm' => ''],
    'tieuDe' => $tuKhoa !== '' ? 'Kết quả tìm kiếm cho “' . $tuKhoa . '”' : 'Tìm kiếm',
    'moTa' => $tuKhoa === ''
        ? 'Nhập từ khoá vào ô tìm kiếm ở đầu trang để tìm dự án và bài viết.'
        : 'Tìm thấy ' . $duAn->count() . ' dự án và ' . $baiViet->count() . ' bài viết.',
])

<div class="nx__container" style="padding-bottom:40px">
    @if($tuKhoa !== '' && !$duAn->count() && !$baiViet->count())
        <div class="nx-empty">Không tìm thấy nội dung nào khớp với “{{ $tuKhoa }}”.</div>
    @endif

    @if($duAn->count())
        <section class="nx__section" style="padding-top:0">
            <div class="nx__head">
                <h2 class="nx__heading">Dự án</h2>
                <a href="{{ url('/du-an?tu-khoa=' . urlencode($tuKhoa)) }}" class="nx__more">
                    Xem trong trang dự án
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
                </a>
            </div>

            <div class="nx-project-grid nx-project-grid--4">
                @foreach($duAn as $d)
                    @include('frontend.noxh.component.project-card', ['duAn' => $d])
                @endforeach
            </div>
        </section>
    @endif

    @if($baiViet->count())
        <section class="nx__section">
            <div class="nx__head">
                <h2 class="nx__heading">Bài viết</h2>
            </div>

            <div class="nx-article-grid">
                @foreach($baiViet as $bai)
                    <article class="nx-article">
                        <a href="{{ url('/tin-tuc/' . $bai->canonical) }}" class="nx-article__media">
                            @if($bai->image)<img src="{{ $bai->image }}" alt="{{ $bai->name }}" loading="lazy">@endif
                        </a>
                        <div class="nx-article__body">
                            <h3 class="nx-article__title">
                                <a href="{{ url('/tin-tuc/' . $bai->canonical) }}">{{ $bai->name }}</a>
                            </h3>
                            @if($bai->description)
                                <p class="nx-article__description">{{ \Illuminate\Support\Str::words(strip_tags($bai->description), 22, '…') }}</p>
                            @endif
                            <div class="nx-article__meta">
                                <span>
                                    @include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 13])
                                    {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
</div>
@endsection
