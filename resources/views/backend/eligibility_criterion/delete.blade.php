@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('eligibility.criterion.destroy', $criterion->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa tiêu chí: <strong>{{ $criterion->label }}</strong></p>
                        <p>Bảng kết quả sẽ còn ít hơn một dòng và điểm đánh giá đổi mẫu số. Các lượt đã chấm trước đó giữ nguyên kết quả cũ.</p>
                        <p>Nếu chỉ muốn tạm ẩn, hãy quay lại và tắt công tắc ở cột Tình trạng thay vì xóa.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Tên tiêu chí</label>
                                    <input type="text" class="form-control" value="{{ $criterion->label }}" readonly>
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
