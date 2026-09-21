@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('eligibility.check.destroy', $check->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-6">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa lượt kiểm tra <strong>{{ $check->code }}</strong> của {{ $check->name ?: 'khách không để lại tên' }} — {{ $check->phone ?: 'không có số' }}.</p>
                        <p class="text-danger">
                            Đây là dữ liệu khách hàng để lại. Xóa đi là mất luôn đầu mối liên hệ và
                            khách sẽ không tra cứu lại được bằng mã này.
                        </p>
                        <p>Không thể khôi phục sau khi xóa.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <button class="btn btn-danger" type="submit" name="send" value="send">Xóa dữ liệu</button>
        </div>
    </div>
</form>
