@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Tư vấn' => ''],
    'tieuDe' => 'Đăng ký tư vấn miễn phí',
    'moTa' => 'Chuyên viên pháp lý của NOXH.vn hỗ trợ bạn từ khâu kiểm tra điều kiện đến khi nhận nhà.',
])

<div class="nx-listing nx-listing--right">
    <div>
        <div class="nx-panel">
            <h2 class="nx-panel__title">Chúng tôi hỗ trợ những gì?</h2>
            <ul class="nx-ticks nx-ticks--green">
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17]) Tư vấn điều kiện mua nhà ở xã hội theo quy định mới nhất</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17]) Kiểm tra và hoàn thiện hồ sơ trước khi nộp</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17]) Chọn dự án phù hợp với khả năng tài chính</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17]) Hướng dẫn thủ tục vay vốn ưu đãi</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17]) Đồng hành theo dõi tiến trình xét duyệt</li>
            </ul>
        </div>

        <div class="nx-panel">
            <h2 class="nx-panel__title">Quy trình hỗ trợ</h2>
            <ol class="nx-next-steps">
                <li><strong>Tiếp nhận thông tin</strong><span>Bạn để lại số điện thoại, chuyên viên liên hệ trong giờ hành chính.</span></li>
                <li><strong>Tư vấn điều kiện</strong><span>Đánh giá khả năng đáp ứng điều kiện mua NOXH của bạn.</span></li>
                <li><strong>Chuẩn bị hồ sơ</strong><span>Hướng dẫn chuẩn bị đầy đủ giấy tờ theo đúng quy định.</span></li>
                <li><strong>Nộp và theo dõi</strong><span>Hỗ trợ nộp hồ sơ và theo dõi đến khi có kết quả.</span></li>
            </ol>
        </div>
    </div>

    <aside>
        @include('frontend.noxh.component.lead-form', [
            'tieuDe' => 'Đăng ký tư vấn',
            'moTa' => 'Hoàn toàn miễn phí, không ràng buộc.',
            'nguon' => 'advise',
        ])

        @include('frontend.noxh.component.expert-box')
    </aside>
</div>
@endsection
