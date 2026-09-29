{{--
    Khoi "Tin tuc noi bat" o cot phai trang danh sach du an.

    Tham so:
      $tinTuc - bai viet lay tu PostQuery::moiNhat()
--}}
@if(count($tinTuc ?? []))
    <section class="nx-panel nx-tin-ben">
        <h2 class="nx-panel__title">
            {{ $intro['projectaside_news_heading'] ?? 'Tin tức nổi bật' }}
            <a href="{{ url('/tin-tuc') }}">
                {{ $intro['projectaside_news_more_text'] ?? 'Xem thêm' }}
                @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 13])
            </a>
        </h2>

        @foreach($tinTuc as $bai)
            @php $url = url('/tin-tuc/' . $bai->canonical); @endphp
            <article class="nx-tin-ben__muc">
                <a href="{{ $url }}" class="nx-tin-ben__anh" tabindex="-1" aria-hidden="true">
                    <img src="{{ nx_anh($bai->image ?? null, 'tin-tuc') }}" alt="" loading="lazy" decoding="async">
                </a>

                <div>
                    <h3><a href="{{ $url }}">{{ $bai->name }}</a></h3>
                    <time datetime="{{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('Y-m-d') }}">
                        {{ \Illuminate\Support\Carbon::parse($bai->created_at)->format('d/m/Y') }}
                    </time>
                </div>
            </article>
        @endforeach
    </section>
@endif
