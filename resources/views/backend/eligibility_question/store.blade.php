@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('eligibility.question.store')
        : route('eligibility.question.update', $question->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Nội dung câu hỏi <span class="text-danger">(*)</span></label>
                                    <textarea name="question" rows="3" class="form-control">{{ old('question', ($question->question) ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Nhóm câu hỏi <span class="text-danger">(*)</span></label>
                                    <select name="group" class="form-control">
                                        @foreach(\App\Models\EligibilityQuestion::NHOM as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('group', ($question->group) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Quyết định câu hỏi nằm ở tab nào ngoài website</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Kiểu trả lời <span class="text-danger">(*)</span></label>
                                    <select name="input_type" class="form-control">
                                        @foreach(\App\Models\EligibilityQuestion::KIEU_NHAP as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('input_type', ($question->input_type) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Trọng số điểm</label>
                                    <input type="number" name="weight" value="{{ old('weight', ($question->weight) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Câu quan trọng đặt trọng số cao hơn</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Tên tiêu chí ở bảng kết quả</label>
                                    <input type="text" name="criteria_label" value="{{ old('criteria_label', ($question->criteria_label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Chưa sở hữu nhà ở</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Ghi chú hướng dẫn</label>
                                    <textarea name="hint" rows="3" class="form-control">{{ old('hint', ($question->hint) ?? '') }}</textarea>
                                    <small class="text-muted">Hiện dưới câu hỏi để người trả lời hiểu đúng</small>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="required" value="1" {{ old('required', ($question->required) ?? 0) ? 'checked' : '' }}>
                                        Bắt buộc trả lời
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($question->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($question->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($question->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
                                    </select>
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
