@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('investor.destroy', $investor->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa chủ đầu tư: <strong>{{ \Illuminate\Support\Str::limit($investor->name, 120) }}</strong></p>
                        <p>Nếu chỉ muốn tạm ẩn, hãy quay lại và tắt công tắc ở cột Tình trạng thay vì xóa.</p>
                        <p>Không thể khôi phục sau khi xóa.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Chủ đầu tư</label>
                                    <textarea class="form-control" rows="2" readonly>{{ $investor->name }}</textarea>
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
