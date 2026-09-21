@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.crumb', [
    'crumbs' => ['Tin tức' => url('/tin-tuc'), \Illuminate\Support\Str::limit($bai->name, 60) => ''],
])

<div class="nx-listing nx-listing--right">
    <article>
        <div class="nx-panel">
            <h1 class="nx__heading" style="font-size:26px;text-transform:none">{{ $bai->name }}</h1>

            <div class="nx-qa-item__meta" style="margin-bottom:16px">
                <span>
                    @include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 13])
                    {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}
                </span>
            </div>

            @if($bai->image)
                <img src="{{ $bai->image }}" alt="{{ $bai->name }}"
                     style="width:100%;height:auto;border-radius:10px;margin-bottom:18px">
            @endif

            @if($bai->description)
                <p style="font-size:16px;color:#12243b;font-weight:500;line-height:1.7">
                    {{ strip_tags($bai->description) }}
                </p>
            @endif

            <div class="nx-prose">{!! $bai->content !!}</div>
        </div>
    </article>

    <aside>
        @include('frontend.noxh.component.expert-box')

        @if($khac->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Bài viết khác</h2>
                @foreach($khac as $b)
                    <div class="nx-news-item">
                        <a href="{{ url('/tin-tuc/' . $b->canonical) }}" class="nx-news-item__thumb">
                            @if($b->image)<img src="{{ $b->image }}" alt="{{ $b->name }}" loading="lazy">@endif
                        </a>
                        <div>
                            <h3 class="nx-news-item__title">
                                <a href="{{ url('/tin-tuc/' . $b->canonical) }}">{{ $b->name }}</a>
                            </h3>
                            <time>{{ \Illuminate\Support\Carbon::parse($b->created_at)->format('d/m/Y') }}</time>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </aside>
</div>
@endsection
