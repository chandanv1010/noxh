@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => [$tieuDe => ''],
    'tieuDe' => $tieuDe,
])

<div class="nx-listing nx-listing--right">
    <div class="nx-panel">
        @if(trim(strip_tags($noiDung)) !== '')
            <div class="nx-prose">{!! $noiDung !!}</div>
        @else
            <div class="nx-empty">
                Nội dung trang này chưa được nhập.
                <br><span style="font-size:13px">Quản trị cập nhật trong mục Giới thiệu của trang quản trị.</span>
            </div>
        @endif
    </div>

    <aside>
        @include('frontend.noxh.component.expert-box')
    </aside>
</div>
@endsection
