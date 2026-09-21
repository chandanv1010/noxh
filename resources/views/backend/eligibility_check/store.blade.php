@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['edit']['title']])

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-4">
            <div class="ibox">
                <div class="ibox-title"><h5>Thông tin lượt kiểm tra</h5></div>
                <div class="ibox-content">
                    <table class="table table-bordered">
                        <tbody>
                            <tr><th style="width:150px;">Mã tra cứu</th><td><strong>{{ $check->code }}</strong></td></tr>
                            <tr><th>Họ và tên</th><td>{{ $check->name ?: '—' }}</td></tr>
                            <tr><th>Điện thoại</th><td>@if($check->phone)<a href="tel:{{ $check->phone }}">{{ $check->phone }}</a>@else — @endif</td></tr>
                            <tr><th>Email</th><td>{{ $check->email ?: '—' }}</td></tr>
                            <tr><th>Điểm</th><td><strong>{{ $check->score_percent }}%</strong></td></tr>
                            <tr><th>Kết luận</th><td><span class="label label-{{ $check->mauMuc() }}">{{ $check->tenMuc() }}</span></td></tr>
                            <tr><th>Số tiêu chí</th><td>Đạt {{ $check->passed }} · Cần kiểm tra {{ $check->unclear }} · Không đạt {{ $check->failed }}</td></tr>
                            <tr><th>Hạn tra cứu</th><td>{{ $check->expires_at ? $check->expires_at->format('d/m/Y') : '—' }} @unless($check->conHan())<span class="label label-default">Đã hết hạn</span>@endunless</td></tr>
                            <tr><th>Đồng ý chính sách</th><td>{{ $check->consent ? 'Có' : 'Không' }}</td></tr>
                            <tr><th>Thời điểm</th><td>{{ $check->created_at ? $check->created_at->format('H:i d/m/Y') : '' }}</td></tr>
                            <tr><th>Địa chỉ IP</th><td>{{ $check->ip ?: '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="ibox">
                <div class="ibox-title"><h5>Câu trả lời của khách</h5></div>
                <div class="ibox-content">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Câu hỏi</th>
                                <th style="width:200px;">Trả lời</th>
                                <th style="width:140px;" class="text-center">Kết luận</th>
                                <th style="width:80px;" class="text-center">Điểm</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($check->answers as $tl)
                                <tr>
                                    <td>{{ $tl->question->question ?? '(câu hỏi đã bị xóa)' }}</td>
                                    <td>{{ $tl->option->label ?? $tl->answer_value ?: '—' }}</td>
                                    <td class="text-center">
                                        <span class="label label-{{ $tl->verdict === 'pass' ? 'success' : ($tl->verdict === 'fail' ? 'danger' : 'warning') }}">
                                            {{ \App\Models\EligibilityOption::KET_LUAN[$tl->verdict] ?? $tl->verdict }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $tl->score }}</td>
                                </tr>
                            @endforeach

                            @if(!$check->answers->count())
                                <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">Lượt kiểm tra này không có câu trả lời nào được lưu.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="text-right mb15">
        <a href="{{ route('eligibility.check.index') }}" class="btn btn-primary">Quay lại danh sách</a>
    </div>
</div>
