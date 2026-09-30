@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('eligibility.panel.destroy', $panel->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa tấm: <strong>{{ $panel->heading ?: 'Tấm chỉ có tranh' }}</strong></p>
                        <p>Tấm này nằm ở cột phải của bước kiểm tra, xóa đi thì cột phải ngắn lại chứ không ảnh hưởng tới câu hỏi hay đáp án.</p>
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
                                    <label class="control-label text-left">Tiêu đề của tấm</label>
                                    <input type="text" class="form-control" value="{{ $panel->heading }}" readonly>
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
