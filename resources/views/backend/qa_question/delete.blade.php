@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['delete']['title']])

<form action="{{ route('qa.question.destroy', $question->id) }}" method="post" class="box">
    @csrf
    @method('DELETE')
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-6">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Bạn đang muốn xóa câu hỏi: <strong>{{ \Illuminate\Support\Str::limit($question->title, 150) }}</strong></p>
                        <p>Người gửi: {{ $question->asker_name ?: 'không rõ' }} — {{ $question->asker_phone ?: 'không có số' }}</p>
                        <p class="text-danger">Xóa câu hỏi thì câu trả lời đi kèm cũng mất theo.</p>
                        <p>Nếu chỉ muốn giấu khỏi website, hãy quay lại và tắt công tắc ở cột Hiển thị.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right mb15">
            <button class="btn btn-danger" type="submit" name="send" value="send">Xóa dữ liệu</button>
        </div>
    </div>
</form>
