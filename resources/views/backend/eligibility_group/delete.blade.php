@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('eligibility.group.destroy', $group->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa tình huống: <strong>{{ $group->label }}</strong></p>
                        <p>Các đáp án nằm trong tình huống này <strong>không bị xóa theo</strong>, chỉ bị gỡ khỏi tấm. Bạn xếp lại chúng sang tình huống khác ở màn hình Đáp án.</p>
                        <p>Không thể khôi phục tấm sau khi xóa.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Tên tình huống</label>
                                    <input type="text" class="form-control" value="{{ $group->label }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-right mb15">
            <button class="btn btn-danger" type="submit" name="send" value="send">Xóa dữ liệu</button>
        </div>
    </div>
</form>
