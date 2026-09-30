@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/tin-tuc-fix.webp (cot giua cua trang chi tiet).
    $ngayBai = \Illuminate\Support\Carbon::parse($bai->created_at);
    $luot = (int) ($bai->viewed ?? 0);
    $duongBai = url('/tin-tuc/' . $bai->canonical);

    // Tu khoa lay tu o "Meta keyword" cua bai - khong co bang tu khoa rieng
    // nen dung lai o nay, quan tri go cach nhau dau phay.
    $tuKhoa = array_values(array_filter(array_map(
        'trim',
        explode(',', (string) $bai->meta_keyword)
    ), fn ($t) => $t !== ''));

    $moTaBai = trim(strip_tags((string) $bai->description));
    $chuThich = trim((string) ($bai->image_caption ?? ''));

    $duongDan = ['Tin tức' => url('/tin-tuc')];

    if ($chuyenMuc) {
        $duongDan[$chuyenMuc->name] = url('/tin-tuc/chuyen-muc/' . $chuyenMuc->canonical);
    }

    $duongDan[\Illuminate\Support\Str::limit($bai->name, 46)] = '';
@endphp

@section('content')
<div class="nx-tin">
    @include('frontend.noxh.component.news-band', ['the' => 'p'])

    <div class="nx__container">
        @include('frontend.noxh.component.crumb', ['crumbs' => $duongDan])

        <div class="nx-tin__luoi">
            <aside class="nx-tin__trai">
                @include('frontend.noxh.component.news-aside-left')
            </aside>

            <main class="nx-tin__giua">
                <article class="nx-panel nx-tin-bai">
                    @if($chuyenMuc)
                        <a href="{{ url('/tin-tuc/chuyen-muc/' . $chuyenMuc->canonical) }}"
                           class="nx-tin-nhan"
                           @if($chuyenMuc->color) style="background: {{ $chuyenMuc->color }}" @endif>
                            {{ $chuyenMuc->name }}
                        </a>
                    @endif

                    <h1 class="nx-tin-bai__ten">{{ $bai->name }}</h1>

                    <div class="nx-tin-bai__dau">
                        <div class="nx-tin-meta">
                            <span>
                                @include('frontend.noxh.component.icon', ['name' => 'calendar-line', 'size' => 15])
                                <time datetime="{{ $ngayBai->toDateString() }}">{{ $ngayBai->format('d/m/Y') }}</time>
                            </span>

                            @if($luot > 0)
                                <span>
                                    @include('frontend.noxh.component.icon', ['name' => 'eye-line', 'size' => 15])
                                    {{ so_rut_gon($luot) }} {{ $intro['news_view_text'] ?? 'lượt xem' }}
                                </span>
                            @endif
                        </div>

                        <div class="nx-tin-chia">
                            @if(!empty($intro['news_share_label']))
                                <span>{{ $intro['news_share_label'] }}</span>
                            @endif

                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($duongBai) }}"
                               class="nx-tin-chia__o nx-tin-chia__o--fb"
                               target="_blank" rel="noopener nofollow" aria-label="Facebook">
                                @include('frontend.noxh.component.social-icon', ['ten' => 'facebook'])
                            </a>

                            <a href="https://sp.zalo.me/plugins/share?u={{ urlencode($duongBai) }}"
                               class="nx-tin-chia__o nx-tin-chia__o--zalo"
                               target="_blank" rel="noopener nofollow" aria-label="Zalo">
                                @include('frontend.noxh.component.social-icon', ['ten' => 'zalo'])
                            </a>

                            <button type="button" class="nx-tin-chia__o nx-tin-chia__o--link"
                                    data-nx-chep="{{ $duongBai }}"
                                    data-nx-chep-xong="{{ $intro['news_copy_done_text'] ?? 'Đã chép đường dẫn' }}"
                                    aria-label="{{ $intro['news_copy_done_text'] ?? 'Chép đường dẫn' }}">
                                @include('frontend.noxh.component.icon', ['name' => 'link', 'size' => 17])
                            </button>
                        </div>
                    </div>

                    @if($bai->image)
                        <figure class="nx-tin-bai__anh">
                            <img src="{{ $bai->image }}" alt="{{ $bai->name }}">
                            @if($chuThich !== '')
                                <figcaption>{{ $chuThich }}</figcaption>
                            @endif
                        </figure>
                    @endif

                    {{-- Noi dung co the rat dai. Nut "xem them" do JS gan vao
                         khi do duoc la noi dung vuot qua chieu cao cho phep -
                         tat JS thi bai viet hien nguyen ven, khong bi cut. --}}
                    <div class="nx-tin-bai__noi" data-nx-mo-rong
                         data-nx-chu-mo="{{ $intro['news_expand_text'] ?? 'Xem thêm nội dung' }}"
                         data-nx-chu-thu="{{ $intro['news_collapse_text'] ?? 'Thu gọn nội dung' }}">
                        <div class="nx-prose">
                            @if($moTaBai !== '')
                                <p class="nx-tin-bai__mo">{{ $moTaBai }}</p>
                            @endif

                            {!! $bai->content !!}
                        </div>
                    </div>

                    @if(count($tuKhoa))
                        <div class="nx-tin-khoa">
                            @if(!empty($intro['news_tag_heading']))
                                <span class="nx-tin-khoa__dau">{{ $intro['news_tag_heading'] }}</span>
                            @endif

                            @foreach($tuKhoa as $t)
                                <a href="{{ url('/tim-kiem?tu-khoa=' . urlencode($t)) }}">{{ $t }}</a>
                            @endforeach
                        </div>
                    @endif
                </article>
            </main>

            <aside class="nx-tin__phai">
                @include('frontend.noxh.component.news-aside-right')
            </aside>
        </div>
    </div>
</div>
@endsection
