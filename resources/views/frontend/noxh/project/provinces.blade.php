@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Dự án' => url('/du-an'), 'Theo tỉnh/thành' => ''],
    'tieuDe' => 'Nhà ở xã hội theo tỉnh / thành phố',
    'moTa' => 'Chọn tỉnh/thành để xem toàn bộ dự án nhà ở xã hội đang triển khai tại khu vực đó.',
])

<div class="nx__container" style="padding-bottom:40px">
    @if($danhSach->count())
        <div class="nx-topic-grid">
            @foreach($danhSach as $t)
                <a href="{{ url('/du-an?province_code=' . $t->province_code) }}" class="nx-useful-card">
                    <span class="nx-useful-card__icon">
                        @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 24])
                    </span>
                    <strong>{{ $t->province_name }}</strong>
                    <p>{{ $t->so_du_an }} dự án</p>
                    <em>Xem dự án →</em>
                </a>
            @endforeach
        </div>
    @else
        <div class="nx-empty">Chưa có dự án nào được đăng.</div>
    @endif
</div>
@endsection
