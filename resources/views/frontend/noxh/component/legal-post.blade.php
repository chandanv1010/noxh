{{--
    Mot the bai viet phap ly.

    Nhan "NOI BAT" chay theo o "Đề xuất" cua chinh bai viet trong admin
    (posts.recommend) chu khong phai "the dau tien trong hang": doi thu tu
    bai la nhan van di theo dung bai duoc chon.

    Tham so: $bai, $intro
--}}
@php
    $duong = url('/tin-tuc/' . $bai->canonical);
    $luot = (int) ($bai->viewed ?? 0);
@endphp

<article class="nx-lpost">
    <a href="{{ $duong }}" class="nx-lpost__anh">
        @if($bai->image)
            <img src="{{ $bai->image }}" alt="{{ $bai->name }}" loading="lazy">
        @endif

        @if((string) ($bai->recommend ?? '') === '2' && !empty($intro['legal_post_hot_text']))
            <span class="nx-lpost__nhan">{{ $intro['legal_post_hot_text'] }}</span>
        @endif
    </a>

    <h3 class="nx-lpost__ten"><a href="{{ $duong }}">{{ $bai->name }}</a></h3>

    @if($bai->description)
        <p class="nx-lpost__mo-ta">
            {{ \Illuminate\Support\Str::words(strip_tags($bai->description), 18, '…') }}
        </p>
    @endif

    <div class="nx-lpost__chan">
        <span>
            @include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 14])
            {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}
        </span>

        @if($luot > 0)
            <span>
                @include('frontend.noxh.component.icon', ['name' => 'eye', 'size' => 14])
                {{ so_rut_gon($luot) }} {{ $intro['legal_post_view_text'] ?? 'lượt xem' }}
            </span>
        @endif
    </div>
</article>
