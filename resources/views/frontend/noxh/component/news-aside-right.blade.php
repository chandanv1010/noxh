{{--
    Cot phai cua hai trang tin tuc: khoi "Bài viết liên quan" va the chuyen
    gia. Trang danh muc va trang chi tiet dung chung khoi nay.

    $lienQuan - cac bai viet o cot phai
--}}
@if($lienQuan->count())
    <div class="nx-panel nx-tin-lq">
        <h2 class="nx-tin-lq__dau">
            {{ $intro['news_related_heading'] ?? 'Bài viết liên quan' }}
            <a href="{{ url('/tin-tuc') }}">
                {{ $intro['news_related_all_text'] ?? 'Xem tất cả' }}
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 14])
            </a>
        </h2>

        @foreach($lienQuan as $b)
            <a href="{{ url('/tin-tuc/' . $b->canonical) }}" class="nx-tin-lq__o">
                <span class="nx-tin-lq__anh">
                    @if($b->image)
                        <img src="{{ $b->image }}" alt="{{ $b->name }}" loading="lazy">
                    @endif
                </span>
                <span class="nx-tin-lq__chu">
                    <strong>{{ $b->name }}</strong>
                    <time datetime="{{ \Illuminate\Support\Carbon::parse($b->created_at)->toDateString() }}">
                        {{ \Illuminate\Support\Carbon::parse($b->created_at)->format('d/m/Y') }}
                    </time>
                </span>
            </a>
        @endforeach
    </div>
@endif

@include('frontend.noxh.component.expert-box')
