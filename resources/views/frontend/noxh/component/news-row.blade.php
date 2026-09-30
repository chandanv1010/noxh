{{--
    Mot dong o trang danh muc tin: anh ben trai, noi dung ben phai. Moi dong
    mot tin - KHONG phai luoi the.

    $bai - bai viet
    $muc - chuyen muc cua bai (co the la null)
--}}
@php
    $duong = url('/tin-tuc/' . $bai->canonical);
    $ngayBai = \Illuminate\Support\Carbon::parse($bai->created_at);
    $tomTat = trim(strip_tags((string) $bai->description));
    $luot = (int) ($bai->viewed ?? 0);
@endphp

<article class="nx-tin-dong">
    <a href="{{ $duong }}" class="nx-tin-dong__anh" tabindex="-1" aria-hidden="true">
        @if($bai->image)
            <img src="{{ $bai->image }}" alt="" loading="lazy">
        @endif
    </a>

    <div class="nx-tin-dong__chu">
        @if($muc)
            <a href="{{ url('/tin-tuc/chuyen-muc/' . $muc->canonical) }}" class="nx-tin-nhan"
               @if($muc->color) style="background: {{ $muc->color }}" @endif>{{ $muc->name }}</a>
        @endif

        <h2 class="nx-tin-dong__ten"><a href="{{ $duong }}">{{ $bai->name }}</a></h2>

        @if($tomTat !== '')
            <p class="nx-tin-dong__tom">{{ \Illuminate\Support\Str::words($tomTat, 32, '…') }}</p>
        @endif

        <div class="nx-tin-meta">
            <span>
                @include('frontend.noxh.component.icon', ['name' => 'calendar-line', 'size' => 14])
                <time datetime="{{ $ngayBai->toDateString() }}">{{ $ngayBai->format('d/m/Y') }}</time>
            </span>

            @if($luot > 0)
                <span>
                    @include('frontend.noxh.component.icon', ['name' => 'eye-line', 'size' => 14])
                    {{ so_rut_gon($luot) }} {{ $intro['news_view_text'] ?? 'lượt xem' }}
                </span>
            @endif
        </div>
    </div>
</article>
