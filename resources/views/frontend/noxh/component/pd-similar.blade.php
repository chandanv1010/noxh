{{--
    Mot the trong khoi "DU AN TUONG TU TAI <tinh>".

    The nay ngan hon the du an o trang danh sach: chi anh, ten va mot dong
    gia, kem nut mui ten tron de len goc anh.

    Tham so: $d (mot dong tu ProjectQuery)
--}}
@php
    $gia = khoang_so($d->price_from, $d->price_to, ' triệu/m²', '');
    $duong = url('/du-an/' . $d->canonical);
@endphp

<article class="nx-pd-tt">
    <a href="{{ $duong }}" class="nx-pd-tt__anh" tabindex="-1" aria-hidden="true">
        <img src="{{ nx_anh($d->image, 'du-an') }}" alt="" loading="lazy" decoding="async">
    </a>

    <a href="{{ $duong }}" class="nx-pd-tt__mui" aria-label="Xem dự án {{ $d->name }}">
        @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 18])
    </a>

    <div class="nx-pd-tt__chu">
        <h3><a href="{{ $duong }}">{{ $d->name }}</a></h3>

        @if($gia !== '')
            <p>
                @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 15])
                {{ trim(($intro['projectdetail_similar_price_prefix'] ?? 'Từ') . ' ' . $gia) }}
            </p>
        @endif
    </div>
</article>
