@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['edit']['title']])
@include('backend.dashboard.component.formError')

@php $traLoi = $question->answers->first(); @endphp

<form action="{{ route('qa.question.update', $question->id) }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-8">
                <div class="ibox">
                    <div class="ibox-title"><h5>Câu hỏi</h5></div>
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Nội dung câu hỏi <span class="text-danger">(*)</span></label>
                                    <textarea name="title" rows="2" class="form-control">{{ old('title', $question->title) }}</textarea>
                                    <small class="text-muted">Có thể sửa lại cho gọn trước khi đăng, nhưng đừng đổi ý của người hỏi.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Diễn giải thêm</label>
                                    <textarea name="content" rows="4" class="form-control">{{ old('content', $question->content) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-title"><h5>Câu trả lời của chuyên gia</h5></div>
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Nội dung trả lời</label>
                                    <textarea name="answer_content" class="ck-editor" id="ck_answer" data-height="260">{{ old('answer_content', $traLoi->content ?? '') }}</textarea>
                                    <small class="text-muted">Để trống thì câu hỏi vẫn ở trạng thái chưa trả lời.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Người trả lời</label>
                                    <select name="expert_id" class="form-control">
                                        <option value="">[Không ghi tên chuyên gia]</option>
                                        @foreach($chuyenGia as $cg)
                                            <option value="{{ $cg->id }}" {{ (int) old('expert_id', $traLoi->expert_id ?? 0) === $cg->id ? 'selected' : '' }}>{{ $cg->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ibox">
                    <div class="ibox-title"><h5>Người gửi</h5></div>
                    <div class="ibox-content">
                        {{-- Chi doc: day la thong tin ban doc tu dien ngoai website. --}}
                        <table class="table table-bordered">
                            <tbody>
                                <tr><th style="width:110px;">Họ và tên</th><td>{{ $question->asker_name ?: '—' }}</td></tr>
                                <tr><th>Điện thoại</th><td>@if($question->asker_phone)<a href="tel:{{ $question->asker_phone }}">{{ $question->asker_phone }}</a>@else — @endif</td></tr>
                                <tr><th>Email</th><td>{{ $question->asker_email ?: '—' }}</td></tr>
                                <tr><th>Lượt xem</th><td>{{ $question->view_count }}</td></tr>
                                <tr><th>Ngày gửi</th><td>{{ $question->created_at ? $question->created_at->format('H:i d/m/Y') : '' }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái xử lý <span class="text-danger">(*)</span></label>
                                    <select name="status" class="form-control">
                                        @foreach($trangThai as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('status', $question->status) === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $question->is_featured) ? 'checked' : '' }}>
                                        Câu hỏi nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Hiển thị ngoài website</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', $question->publish) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', $question->publish) == 1 ? 'selected' : '' }}>Ẩn</option>
                                    </select>
                                    <small class="text-muted">Câu hỏi mới mặc định là Ẩn, phải duyệt mới hiện ra ngoài.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('backend.dashboard.component.button')
    </div>
</form>
