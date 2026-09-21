@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Pháp lý NOXH' => ''],
    'tieuDe' => $intro['legal_heading'] ?? 'Phòng pháp lý NOXH',
    'moTa' => $intro['legal_description'] ?? 'Giải đáp pháp lý – Hỗ trợ hồ sơ – An tâm mua nhà ở xã hội.',
])

<div class="nx-listing nx-listing--right">
    <div>
        <div class="nx-panel">
            <h2 class="nx-panel__title">Chủ đề pháp lý NOXH</h2>
            <div class="nx-topic-grid">
                @foreach([
                    ['users', 'Đối tượng & điều kiện', 'Ai được mua NOXH? Điều kiện về nhà ở, thu nhập, cư trú, hộ khẩu...', '/phap-ly-noxh'],
                    ['home', 'Mua bán & chuyển nhượng', 'Quy định mua bán NOXH, thời hạn chuyển nhượng, tặng cho, thừa kế...', '/phap-ly-noxh'],
                    ['file-text', 'Hợp đồng & thanh toán', 'Hợp đồng mua bán, đặt cọc, tiến độ thanh toán, vay vốn ưu đãi...', '/phap-ly-noxh'],
                    ['clipboard', 'Hồ sơ & thủ tục', 'Hồ sơ cần chuẩn bị, quy trình nộp hồ sơ, thẩm định và xét duyệt...', '/ho-so'],
                    ['scale', 'Chính sách & văn bản', 'Văn bản pháp luật, nghị định, thông tư, công văn hướng dẫn mới nhất...', '/phap-ly-noxh/van-ban'],
                ] as $o)
                    <a href="{{ url($o[3]) }}" class="nx-useful-card">
                        <span class="nx-useful-card__icon">
                            @include('frontend.noxh.component.icon', ['name' => $o[0], 'size' => 24])
                        </span>
                        <strong>{{ $o[1] }}</strong>
                        <p>{{ $o[2] }}</p>
                    </a>
                @endforeach
            </div>
        </div>

        @if($baiViet->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">
                    Bài viết pháp lý mới nhất
                    <a href="{{ url('/tin-tuc') }}">Xem tất cả</a>
                </h2>
                <div class="nx-article-grid">
                    @foreach($baiViet as $bai)
                        <article class="nx-article">
                            <a href="{{ url('/tin-tuc/' . $bai->canonical) }}" class="nx-article__media">
                                @if($bai->image)<img src="{{ $bai->image }}" alt="{{ $bai->name }}" loading="lazy">@endif
                            </a>
                            <div class="nx-article__body">
                                <h3 class="nx-article__title"><a href="{{ url('/tin-tuc/' . $bai->canonical) }}">{{ $bai->name }}</a></h3>
                                <p class="nx-article__description">{{ \Illuminate\Support\Str::words(strip_tags($bai->description), 20, '…') }}</p>
                                <div class="nx-article__meta">
                                    <span>@include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 13]) {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        @if($cauHoi->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">
                    Hỏi đáp pháp lý
                    <a href="{{ url('/hoi-dap') }}">Xem tất cả</a>
                </h2>
                @foreach($cauHoi as $ch)
                    <div class="nx-qa-item">
                        <span class="nx-qa-item__avatar">{{ mb_substr($ch->asker_name ?: 'K', 0, 1) }}</span>
                        <div>
                            <h3 class="nx-qa-item__title"><a href="{{ url('/hoi-dap/' . $ch->id) }}">{{ $ch->title }}</a></h3>
                            <div class="nx-qa-item__meta">
                                <span>{{ $ch->asker_name ?: 'Bạn đọc' }}</span>
                                <span>{{ $ch->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <aside>
        @include('frontend.noxh.component.expert-box')

        <div class="nx-panel">
            <h2 class="nx-panel__title">
                Văn bản pháp luật mới
                <a href="{{ url('/phap-ly-noxh/van-ban') }}">Xem tất cả</a>
            </h2>

            @forelse($vanBan as $vb)
                <div class="nx-legal-item">
                    <span class="nx-legal-item__icon">
                        @include('frontend.noxh.component.icon', ['name' => 'file-text', 'size' => 22])
                    </span>
                    <div class="nx-legal-item__body">
                        <strong>{{ $vb->doc_number ? $vb->doc_number . ' — ' : '' }}{{ $vb->title }}</strong>
                        @if($vb->effective_date)
                            <time>Có hiệu lực từ {{ $vb->effective_date->format('d/m/Y') }}</time>
                        @endif
                    </div>
                    @if($vb->file)
                        <a href="{{ route('noxh.legal.download', $vb->id) }}" class="nx-legal-item__download" title="Tải về">
                            @include('frontend.noxh.component.icon', ['name' => 'download', 'size' => 18])
                        </a>
                    @endif
                </div>
            @empty
                <p class="nx__subheading" style="margin:0">Chưa có văn bản nào.</p>
            @endforelse
        </div>
    </aside>
</div>
@endsection
