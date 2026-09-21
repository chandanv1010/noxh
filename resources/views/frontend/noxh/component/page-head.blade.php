{{--
    Dau cac trang trong: duong dan dieu huong, tieu de, mo ta va (neu co) dai
    bon con so.

    $crumbs, $tieuDe, $moTa, $soLieu
--}}
<section class="nx-page-head">
    @include('frontend.noxh.component.crumb', ['crumbs' => $crumbs ?? []])

    <div class="nx-page-head__inner">
        <div>
            <h1 class="nx-page-head__title">{{ $tieuDe }}</h1>
            @if(!empty($moTa))
                <p class="nx-page-head__description">{!! nl2br(e($moTa)) !!}</p>
            @endif
        </div>

        @if(!empty($soLieu))
            <div class="nx-page-head__stats">
                @foreach($soLieu as $s)
                    <div class="nx-mini-stat">
                        @include('frontend.noxh.component.icon', ['name' => $s['icon'], 'size' => 22])
                        <span>
                            <span class="nx-mini-stat__value">{{ $s['value'] }}</span>
                            <span class="nx-mini-stat__label">{{ $s['label'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
