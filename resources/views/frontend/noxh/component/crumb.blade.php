{{-- Duong dan dieu huong. $crumbs = ['Nhan' => 'url' hoac '' neu la trang hien tai] --}}
<nav class="nx-crumb" aria-label="Đường dẫn">
    <a href="{{ url('/') }}">Trang chủ</a>
    @foreach($crumbs ?? [] as $nhan => $url)
        <span aria-hidden="true">›</span>
        @if($url)
            <a href="{{ $url }}">{{ $nhan }}</a>
        @else
            <span>{{ $nhan }}</span>
        @endif
    @endforeach
</nav>
