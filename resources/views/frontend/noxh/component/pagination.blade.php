{{--
    Phan trang. Laravel sinh san ban Bootstrap nhung giao dien nay khong dung
    Bootstrap, nen ve lai cho khop bo style.
--}}
@if($model->hasPages())
    <nav class="nx-pagination" aria-label="Phân trang">
        @if($model->onFirstPage())
            <span class="is-disabled" aria-hidden="true">‹</span>
        @else
            <a href="{{ $model->previousPageUrl() }}" rel="prev" aria-label="Trang trước">‹</a>
        @endif

        @foreach($model->getUrlRange(max(1, $model->currentPage() - 2), min($model->lastPage(), $model->currentPage() + 2)) as $trang => $url)
            @if($trang === $model->currentPage())
                <span class="is-active" aria-current="page">{{ $trang }}</span>
            @else
                <a href="{{ $url }}">{{ $trang }}</a>
            @endif
        @endforeach

        @if($model->lastPage() > $model->currentPage() + 2)
            <span class="is-disabled">…</span>
            <a href="{{ $model->url($model->lastPage()) }}">{{ $model->lastPage() }}</a>
        @endif

        @if($model->hasMorePages())
            <a href="{{ $model->nextPageUrl() }}" rel="next" aria-label="Trang sau">›</a>
        @else
            <span class="is-disabled" aria-hidden="true">›</span>
        @endif
    </nav>
@endif
