{{--
    Mot tin trong danh sach tin tuc.

    Tham so:
      $bai  - dong bai viet, can co name, canonical, image, created_at.
              Neu co them catalogue_name / catalogue_color thi hien ten
              chuyen muc phia tren tieu de, to dung mau quan tri da chon cho
              chuyen muc do.
      $cot  - true thi xep nam ngang trong khoi ba cot cua trang chu,
              false (mac dinh) thi xep doc thanh danh sach.
--}}
@php
    $url = url('/tin-tuc/' . $bai->canonical);
    $mau = $bai->catalogue_color ?? '';
@endphp

<article class="nx-news-item{{ ($cot ?? false) ? ' nx-news-item--cot' : '' }}">
    <a href="{{ $url }}" class="nx-news-item__thumb" title="{{ $bai->name }}" aria-hidden="true" tabindex="-1">
        <img src="{{ nx_anh($bai->image ?? null, 'tin-tuc') }}" alt="" loading="lazy" decoding="async">
    </a>

    <div>
        @if(!empty($bai->catalogue_name))
            {{-- Mau lay tu o "Mau nhan chuyen muc" trong quan tri. Gia tri da
                 duoc nx_mau_chuyen_muc() loc chi con dang #rrggbb truoc khi
                 den day. --}}
            <a href="{{ url('/tin-tuc') }}" class="nx-news-item__cat"
               @if($mau) style="--nx-cat: {{ $mau }}" @endif>{{ $bai->catalogue_name }}</a>
        @endif

        <h3 class="nx-news-item__title">
            <a href="{{ $url }}">{{ $bai->name }}</a>
        </h3>

        <time datetime="{{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('Y-m-d') }}">
            {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}
        </time>
    </div>
</article>
