<div class="row">
    @php
        $the = [
            ['Dự án phụ trách', $soLieu['duAn'], 'fa-building', 'navy'],
            ['Đang hiển thị', $soLieu['duAnHien'], 'fa-eye', 'primary'],
            ['Bài viết đã gửi', $soLieu['baiViet'], 'fa-newspaper-o', 'info'],
            ['Bài chờ duyệt', $soLieu['baiChoDuyet'], 'fa-clock-o', 'warning'],
        ];
    @endphp
    @foreach($the as [$ten, $so, $bieuTuong, $mau])
        <div class="col-lg-3 col-sm-6">
            <div class="ibox">
                <div class="ibox-content">
                    <h5>{{ $ten }} <i class="fa {{ $bieuTuong }} pull-right text-{{ $mau }}"></i></h5>
                    <h1 class="no-margins">{{ $so }}</h1>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="ibox">
            <div class="ibox-title"><h5>Dự án sửa gần đây</h5></div>
            <div class="ibox-content">
                @if($duAnGanDay->count())
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th style="width:70px">Ảnh</th>
                                <th>Tên dự án</th>
                                <th style="width:150px">Trạng thái</th>
                                <th style="width:110px"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($duAnGanDay as $d)
                                <tr>
                                    <td>
                                        @if($d->image)
                                            <img src="{{ $d->image }}" alt="" style="width:56px;height:40px;object-fit:cover">
                                        @endif
                                    </td>
                                    <td>{{ $d->name }}</td>
                                    <td>
                                        @if((int) $d->publish === 2)
                                            <span class="label label-primary">Đang hiển thị</span>
                                        @else
                                            <span class="label label-default">Đang ẩn</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('sale.project.edit', $d->id) }}" class="btn btn-xs btn-white">Sửa</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">
                        Bạn chưa phụ trách dự án nào. Quản trị viên sẽ gán dự án cho bạn,
                        hoặc bạn có thể <a href="{{ route('sale.project.create') }}">tự thêm dự án mới</a>.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ibox">
            <div class="ibox-title"><h5>Hồ sơ hiển thị ngoài website</h5></div>
            <div class="ibox-content">
                <p class="text-muted" style="font-size:13px">
                    Ảnh, họ tên, chức danh và số điện thoại trong hồ sơ của bạn chính là
                    những gì khách nhìn thấy ở mục <strong>Nhân viên kinh doanh phụ trách</strong>
                    trên trang từng dự án.
                </p>
                <a href="{{ route('sale.profile') }}" class="btn btn-primary btn-sm">Cập nhật hồ sơ</a>
            </div>
        </div>
    </div>
</div>
