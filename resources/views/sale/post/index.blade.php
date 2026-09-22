@php
    $nhan = [
        'pending' => ['warning', 'Chờ duyệt'],
        'approved' => ['primary', 'Đã duyệt'],
        'rejected' => ['danger', 'Bị từ chối'],
    ];
@endphp
<div class="ibox">
    <div class="ibox-title">
        <h5>Bài viết của tôi</h5>
        <div class="ibox-tools">
            <a href="{{ route('sale.post.create') }}" class="btn btn-primary btn-xs">
                <i class="fa fa-plus"></i> Viết bài mới
            </a>
        </div>
    </div>
    <div class="ibox-content">
        {{-- Quan tri bat/tat che do duyet trong Cau hinh he thong. Loi nhac o
             day phai doi theo, khong thi no noi sai. --}}
        <p class="text-muted" style="font-size:13px">
            @if(cai_dat('sale_post_approval', 'on') === 'off')
                Bài viết bạn gửi lên sẽ hiển thị ngay ra website.
            @else
                Bài viết bạn gửi lên sẽ được quản trị duyệt trước khi hiển thị ra website.
                Mỗi lần sửa lại bài, bài sẽ quay về trạng thái chờ duyệt.
            @endif
        </p>

        <form method="get" class="form-inline" style="margin-bottom:16px">
            <input type="text" name="tu-khoa" value="{{ $tuKhoa }}" class="form-control"
                   placeholder="Tìm theo tiêu đề" style="min-width:260px">
            <button class="btn btn-white" type="submit">Tìm</button>
            @if($tuKhoa)
                <a href="{{ route('sale.post.index') }}" class="btn btn-link">Bỏ lọc</a>
            @endif
        </form>

        @if($baiViet->count())
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:80px">Ảnh</th>
                        <th>Tiêu đề</th>
                        <th style="width:130px">Duyệt</th>
                        <th style="width:130px">Hiển thị</th>
                        <th style="width:90px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($baiViet as $b)
                        @php $tt = $nhan[$b->approval_status] ?? ['default', $b->approval_status]; @endphp
                        <tr>
                            <td>
                                @if($b->image)
                                    <img src="{{ $b->image }}" alt="" style="width:64px;height:46px;object-fit:cover">
                                @endif
                            </td>
                            <td>
                                <strong>{{ $b->name }}</strong>
                                <div class="text-muted" style="font-size:12px">/{{ $b->canonical }}</div>
                            </td>
                            <td><span class="label label-{{ $tt[0] }}">{{ $tt[1] }}</span></td>
                            <td>
                                @if((int) $b->publish === 2)
                                    <span class="label label-primary">Đang hiển thị</span>
                                @else
                                    <span class="label label-default">Đang ẩn</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('sale.post.edit', $b->id) }}" class="btn btn-xs btn-white">Sửa</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{ $baiViet->links() }}
        @else
            <p class="text-muted">
                @if($tuKhoa)
                    Không tìm thấy bài viết nào khớp với "{{ $tuKhoa }}".
                @else
                    Bạn chưa gửi bài viết nào.
                @endif
            </p>
        @endif
    </div>
</div>
