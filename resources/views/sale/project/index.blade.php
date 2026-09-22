<div class="ibox">
    <div class="ibox-title">
        <h5>Dự án của tôi</h5>
        <div class="ibox-tools">
            <a href="{{ route('sale.project.create') }}" class="btn btn-primary btn-xs">
                <i class="fa fa-plus"></i> Thêm dự án
            </a>
        </div>
    </div>
    <div class="ibox-content">
        <form method="get" class="form-inline" style="margin-bottom:16px">
            <input type="text" name="tu-khoa" value="{{ $tuKhoa }}" class="form-control"
                   placeholder="Tìm theo tên dự án" style="min-width:260px">
            <button class="btn btn-white" type="submit">Tìm</button>
            @if($tuKhoa)
                <a href="{{ route('sale.project.index') }}" class="btn btn-link">Bỏ lọc</a>
            @endif
        </form>

        @if($duAn->count())
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:80px">Ảnh</th>
                        <th>Tên dự án</th>
                        <th style="width:150px">Tình trạng</th>
                        <th style="width:130px">Hiển thị</th>
                        <th style="width:140px">Vì sao tôi thấy</th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($duAn as $d)
                        <tr>
                            <td>
                                @if($d->image)
                                    <img src="{{ $d->image }}" alt="" style="width:64px;height:46px;object-fit:cover">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $d->name }}</strong>
                                <div class="text-muted" style="font-size:12px">/{{ $d->canonical }}</div>
                            </td>
                            <td>{{ $trangThaiDuAn[$d->status] ?? '—' }}</td>
                            <td>
                                @if((int) $d->publish === 2)
                                    <span class="label label-primary">Đang hiển thị</span>
                                @else
                                    <span class="label label-default">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:12px">
                                {{ (int) $d->user_id === (int) auth()->id() ? 'Tôi tạo' : 'Được giao' }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('sale.project.edit', $d->id) }}" class="btn btn-xs btn-white">Sửa</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $duAn->links() }}
        @else
            <p class="text-muted">
                @if($tuKhoa)
                    Không tìm thấy dự án nào khớp với "{{ $tuKhoa }}".
                @else
                    Bạn chưa phụ trách dự án nào.
                @endif
            </p>
        @endif
    </div>
</div>
