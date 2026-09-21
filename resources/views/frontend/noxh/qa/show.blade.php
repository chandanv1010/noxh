@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.crumb', [
    'crumbs' => ['Hỏi đáp' => url('/hoi-dap'), \Illuminate\Support\Str::limit($cauHoi->title, 60) => ''],
])

<div class="nx-listing nx-listing--right">
    <div>
        <div class="nx-panel">
            <h1 class="nx__heading" style="font-size:22px;text-transform:none">{{ $cauHoi->title }}</h1>

            <div class="nx-qa-item__meta" style="margin-bottom:14px">
                <span>{{ $cauHoi->asker_name ?: 'Bạn đọc' }}</span>
                <span>{{ $cauHoi->created_at?->format('d/m/Y') }}</span>
                <span>@include('frontend.noxh.component.icon', ['name' => 'eye', 'size' => 13]) {{ number_format($cauHoi->view_count, 0, ',', '.') }}</span>
            </div>

            @if($cauHoi->content)
                <div class="nx-prose">{!! nl2br(e($cauHoi->content)) !!}</div>
            @endif
        </div>

        @forelse($cauHoi->answers as $tl)
            <div class="nx-panel">
                <h2 class="nx-panel__title">
                    Trả lời
                    @if($tl->expert)
                        <span style="color:#8695aa;font-size:12.5px;font-weight:500;text-transform:none">
                            {{ $tl->expert->name }}@if($tl->expert->title) — {{ $tl->expert->title }}@endif
                        </span>
                    @endif
                </h2>
                <div class="nx-prose">{!! $tl->content !!}</div>
            </div>
        @empty
            <div class="nx-alert nx-alert--info">
                Câu hỏi này đang chờ chuyên gia trả lời.
            </div>
        @endforelse
    </div>

    <aside>
        @include('frontend.noxh.component.expert-box')

        @if($lienQuan->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Câu hỏi khác</h2>
                @foreach($lienQuan as $ch)
                    <div class="nx-qa-item">
                        <div>
                            <h3 class="nx-qa-item__title">
                                <a href="{{ url('/hoi-dap/' . $ch->id) }}">{{ $ch->title }}</a>
                            </h3>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </aside>
</div>
@endsection
