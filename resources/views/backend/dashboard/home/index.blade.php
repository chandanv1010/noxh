{{--
    Trang dau khu quan tri NOXH.vn.

    Ba khoi, theo dung thu tu nguoi dung can: viec dang cho xu ly, cac con so
    tong quat, roi danh sach khach moi de lai thong tin.
--}}
<div class="wrapper wrapper-content">

    @php
        $tongChoDuyet = array_sum($choDuyet);
    @endphp

    @if($tongChoDuyet > 0)
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-title"><h5>Đang chờ bạn duyệt</h5></div>
                    <div class="ibox-content">
                        @if($choDuyet['baiViet'])
                            <a href="{{ route('post.index') }}" class="btn btn-warning" style="margin-right:8px">
                                <i class="fa fa-newspaper-o"></i>
                                {{ $choDuyet['baiViet'] }} bài viết
                            </a>
                        @endif
                        @if($choDuyet['duAn'])
                            <a href="{{ route('product.index') }}" class="btn btn-warning" style="margin-right:8px">
                                <i class="fa fa-building"></i>
                                {{ $choDuyet['duAn'] }} dự án
                            </a>
                        @endif
                        @if($choDuyet['cauHoi'])
                            <a href="{{ route('qa.question.index') }}" class="btn btn-warning">
                                <i class="fa fa-question-circle"></i>
                                {{ $choDuyet['cauHoi'] }} câu hỏi
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        @foreach($soLieu as $o)
            <div class="col-lg-3 col-sm-6">
                <div class="ibox">
                    <div class="ibox-content">
                        <h5>
                            {{ $o['ten'] }}
                            <i class="fa {{ $o['icon'] }} pull-right text-{{ $o['mau'] }}"></i>
                        </h5>
                        <h1 class="no-margins">{{ number_format($o['so'], 0, ',', '.') }}</h1>
                        <div class="text-muted" style="font-size:12px;margin-top:4px">{{ $o['phu'] }}</div>
                        <a href="{{ $o['route'] }}" style="font-size:12px">Xem chi tiết →</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5>Khách để lại thông tin gần đây</h5>
                    <div class="ibox-tools">
                        <a href="{{ route('contact.index') }}" class="btn btn-primary btn-xs">Xem tất cả</a>
                    </div>
                </div>
                <div class="ibox-content">
                    @if($lienHeMoi->count())
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Họ tên</th>
                                    <th style="width:150px">Điện thoại</th>
                                    <th style="width:160px">Từ trang</th>
                                    <th style="width:120px">Trạng thái</th>
                                    <th style="width:160px">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lienHeMoi as $lh)
                                    <tr>
                                        <td>{{ $lh->name }}</td>
                                        <td>{{ $lh->phone }}</td>
                                        <td class="text-muted">{{ $lh->source ?: '—' }}</td>
                                        <td>
                                            @if($lh->status === 'new')
                                                <span class="label label-warning">Chưa xử lý</span>
                                            @else
                                                <span class="label label-default">{{ $lh->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $lh->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted" style="margin:0">Chưa có khách nào để lại thông tin.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
