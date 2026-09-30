@extends('frontend.noxh.layout')

@php
    // Ban ve: noxh_image/tin-tuc-fix.webp
    //
    // Khong chuoi nao viet cung o day - moi dong chu deu doc tu bang
    // introduces (nhom "Khối 6: Tin tức"), gia tri mac dinh chi la phao cuu
    // sinh khi quan tri lo xoa trang mot o.
    $tenTrang = $chuyenMuc->name ?? ($intro['news_cat_all_text'] ?? 'Tất cả tin tức');

    // Tra nhanh chuyen muc cua tung bai de ve nhan mau, khong phai truy van
    // lai cho moi dong.
    $tra = $danhMuc->keyBy('id');

    $dongDem = strtr(
        (string) ($intro['news_count_text'] ?? '{so} bài viết'),
        ['{so}' => number_format($baiViet->total(), 0, ',', '.')]
    );
@endphp

@section('content')
<div class="nx-tin">
    @include('frontend.noxh.component.news-band', ['the' => 'h1'])

    <div class="nx__container">
        @include('frontend.noxh.component.crumb', [
            'crumbs' => $chuyenMuc
                ? ['Tin tức' => url('/tin-tuc'), $chuyenMuc->name => '']
                : ['Tin tức' => ''],
        ])

        <div class="nx-tin__luoi">
            <aside class="nx-tin__trai">
                @include('frontend.noxh.component.news-aside-left')
            </aside>

            <main class="nx-tin__giua">
                <div class="nx-panel nx-tin-ds">
                    <h2 class="nx-tin-ds__dau">
                        {{ $tenTrang }}
                        @if($baiViet->total())
                            <span>{{ $dongDem }}</span>
                        @endif
                    </h2>

                    @forelse($baiViet as $bai)
                        @include('frontend.noxh.component.news-row', [
                            'bai' => $bai,
                            'muc' => $tra[$bai->post_catalogue_id] ?? null,
                        ])
                    @empty
                        <p class="nx-tin__trong">
                            {{ $intro['news_empty_text'] ?? 'Chưa có bài viết nào.' }}
                        </p>
                    @endforelse
                </div>

                @include('frontend.noxh.component.pagination', ['model' => $baiViet])
            </main>

            <aside class="nx-tin__phai">
                @include('frontend.noxh.component.news-aside-right')
            </aside>
        </div>
    </div>
</div>
@endsection
