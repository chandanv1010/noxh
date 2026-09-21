@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Tin tức' => ''],
    'tieuDe' => $intro['news_heading'] ?? 'Tin tức nhà ở xã hội',
    'moTa' => $intro['news_description'] ?? 'Chính sách, tiến độ dự án và hướng dẫn thủ tục mới nhất.',
])

<div class="nx__container" style="padding-bottom:40px">
    @if($baiViet->count())
        <div class="nx-article-grid">
            @foreach($baiViet as $bai)
                <article class="nx-article">
                    <a href="{{ url('/tin-tuc/' . $bai->canonical) }}" class="nx-article__media">
                        @if($bai->image)<img src="{{ $bai->image }}" alt="{{ $bai->name }}" loading="lazy">@endif
                    </a>
                    <div class="nx-article__body">
                        <h2 class="nx-article__title">
                            <a href="{{ url('/tin-tuc/' . $bai->canonical) }}">{{ $bai->name }}</a>
                        </h2>
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

        @include('frontend.noxh.component.pagination', ['model' => $baiViet])
    @else
        <div class="nx-empty">Chưa có bài viết nào.</div>
    @endif
</div>
@endsection
