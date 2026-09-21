@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('project.faq.destroy', $faq->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-6">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa câu hỏi thường gặp: <strong>{{ \Illuminate\Support\Str::limit($faq->order, 150) }}</strong></p>
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
