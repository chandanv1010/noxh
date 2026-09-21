@extends('frontend.noxh.layout')

@section('content')
@php
    $mau = ['high' => '#16a34a', 'medium' => '#f59e0b', 'low' => '#dc2626'][$luot->result_level] ?? '#1668e3';
    $phanTram = max(0, min(100, (int) $luot->score_percent));
@endphp

@include('frontend.noxh.component.crumb', [
    'crumbs' => ['Kiểm tra điều kiện' => url('/kiem-tra-dieu-kien'), 'Kết quả' => ''],
])

<div class="nx-check">
    <div>
        <div class="nx-panel">
            <div class="nx-score">
                {{-- Vong tron ve bang conic-gradient: mot dong CSS, khong can
                     them thu vien bieu do chi de ve mot vong tron. --}}
                <div class="nx-score__ring"
                     style="background: conic-gradient({{ $mau }} 0% {{ $phanTram }}%, #e3ebf6 {{ $phanTram }}% 100%)">
                    <div class="nx-score__inner">
                        <span class="nx-score__caption">Khả năng đáp ứng</span>
                        <span class="nx-score__level" style="color:{{ $mau }}">{{ $luot->tenMuc() }}</span>
                        <span class="nx-score__percent">{{ $phanTram }}%</span>
                    </div>
                </div>

                <div class="nx-score__text">
                    <h1 class="nx-score__title">
                        @if($luot->result_level === 'high')
                            Anh/Chị có khả năng đáp ứng điều kiện mua Nhà ở xã hội.
                        @elseif($luot->result_level === 'medium')
                            Anh/Chị cần bổ sung thêm thông tin để xác định điều kiện.
                        @else
                            Theo thông tin đã cung cấp, Anh/Chị chưa đáp ứng đủ điều kiện.
                        @endif
                    </h1>
                    <p style="margin:0;color:#4a5a70">
                        Anh/Chị đã đáp ứng <strong>{{ $luot->passed }}/{{ $luot->total_questions }}</strong> tiêu chí.
                        @if($luot->unclear)
                            Có {{ $luot->unclear }} tiêu chí cần kiểm tra hoặc bổ sung thêm.
                        @endif
                    </p>
                </div>
            </div>

            <dl class="nx-result-meta">
                <div>
                    @include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 20])
                    <span><dt>Ngày kiểm tra</dt><dd>{{ $luot->created_at?->format('d/m/Y - H:i') }}</dd></span>
                </div>
                <div>
                    @include('frontend.noxh.component.icon', ['name' => 'clipboard', 'size' => 20])
                    <span><dt>Mã kết quả</dt><dd>{{ $luot->code }}</dd></span>
                </div>
                <div>
                    @include('frontend.noxh.component.icon', ['name' => 'shield-check', 'size' => 20])
                    <span>
                        <dt>Kết quả có hiệu lực</dt>
                        <dd>{{ $luot->conHan() ? 'Đến ' . $luot->expires_at?->format('d/m/Y') : 'Đã hết hạn' }}</dd>
                    </span>
                </div>
            </dl>

            <div class="nx-alert nx-alert--info" style="margin:18px 0 0">
                Kết quả này chỉ mang tính tham khảo. Việc xét duyệt hồ sơ sẽ do cơ quan có thẩm quyền thực hiện.
            </div>
        </div>

        {{-- CHI TIẾT TỪNG TIÊU CHÍ ------------------------------------------ --}}
        @if($luot->answers->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">Chi tiết kết quả</h2>

                <div class="nx-scroll-x">
                <table class="nx-criteria">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nội dung điều kiện</th>
                            <th style="width:150px">Kết quả</th>
                            <th style="width:170px">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($luot->answers as $tl)
                            <tr>
                                <td><span class="nx-criteria__no">{{ $loop->iteration }}</span></td>
                                <td>
                                    {{ $tl->question->criteria_label ?: ($tl->question->question ?? '—') }}
                                    @if($tl->option)
                                        <br><span style="color:#8695aa;font-size:12.5px">{{ $tl->option->label }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="nx-criteria__verdict nx-criteria__verdict--{{ $tl->verdict }}">
                                        @include('frontend.noxh.component.icon', ['name' => $tl->verdict === 'pass' ? 'check-circle' : ($tl->verdict === 'fail' ? 'warning' : 'info'), 'size' => 16])
                                        {{ \App\Models\EligibilityOption::KET_LUAN[$tl->verdict] ?? $tl->verdict }}
                                    </span>
                                </td>
                                <td style="color:#8695aa">
                                    {{ $tl->option->note ?: ($tl->verdict === 'pass' ? 'Đáp ứng điều kiện' : 'Vui lòng cung cấp thêm') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
        @endif

        {{-- DỰ ÁN GỢI Ý ----------------------------------------------------- --}}
        @if($goiY->count())
            <div class="nx-panel">
                <h2 class="nx-panel__title">
                    Dự án phù hợp với Anh/Chị
                    <a href="{{ url('/du-an') }}">Xem tất cả</a>
                </h2>
                <div class="nx-project-grid nx-project-grid--3">
                    @foreach($goiY as $duAn)
                        @include('frontend.noxh.component.project-card', ['duAn' => $duAn])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <aside>
        @include('frontend.noxh.component.expert-box')

        <div class="nx-panel">
            <h2 class="nx-panel__title">Các bước tiếp theo</h2>
            <ol class="nx-next-steps">
                <li><strong>Tư vấn chi tiết</strong><span>Chuyên viên sẽ liên hệ để tư vấn kết quả và giải đáp thắc mắc.</span></li>
                <li><strong>Kiểm tra hồ sơ</strong><span>Hỗ trợ kiểm tra và hoàn thiện hồ sơ theo đúng quy định.</span></li>
                <li><strong>Nộp hồ sơ</strong><span>Hướng dẫn nộp hồ sơ và theo dõi tiến trình xét duyệt.</span></li>
                <li><strong>Nhận kết quả &amp; ký hợp đồng</strong><span>Nhận thông báo, ký hợp đồng và chuẩn bị nhận nhà.</span></li>
            </ol>
        </div>

        @include('frontend.noxh.component.lead-form', [
            'tieuDe' => 'Cần kiểm tra hồ sơ chi tiết?',
            'moTa' => 'Để được đánh giá chính xác và tiết kiệm thời gian, hãy để chuyên viên hỗ trợ bạn.',
            'nguon' => 'eligibility-result',
        ])
    </aside>
</div>
@endsection
