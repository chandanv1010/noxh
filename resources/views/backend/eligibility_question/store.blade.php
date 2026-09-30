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
                                <label class="control-label text-left">Bố cục khối đáp án</label>
                                    <select name="layout" class="form-control">
                                        @foreach(\App\Models\EligibilityQuestion::BO_CUC as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('layout', ($question->layout) ?? 'grid') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">"Chia theo tình huống" thì đáp án xếp vào các tấm ở màn hình Tình huống.</small>
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
                    {{-- Dong chu xam co hinh chu "i" nam duoi luoi dap an
                         (ban ve noxh_image/w-1.jpg). --}}
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Dòng lưu ý dưới lưới đáp án</label>
                                    <textarea name="foot_note" rows="2" class="form-control">{{ old('foot_note', ($question->foot_note) ?? '') }}</textarea>
                                    <small class="text-muted">Ví dụ: Danh mục nhóm đối tượng được xây dựng theo quy định hiện hành.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Dòng lưu ý - dòng thứ hai</label>
                                    <textarea name="foot_note_sub" rows="2" class="form-control">{{ old('foot_note_sub', ($question->foot_note_sub) ?? '') }}</textarea>
                                    <small class="text-muted">Dòng đầu in đậm, dòng này in thường ngay dưới.</small>
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
                        {{-- Hinh tron in tren dau cau hoi (bo cuc "Chia theo tinh
                             huong" - ban ve noxh_image/w-2.jpg). --}}
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Hình tròn trên đầu câu hỏi</label>
                                    <span class="image img-cover image-target"
                                          style="height:110px;padding:10px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($question->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($question->image) ?? '') }}">
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Hình (dùng khi chưa có ảnh)</label>
                                    <select name="icon" class="form-control mb10">
                                        @foreach(\App\Classes\NoxhIcon::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon', ($question->icon) ?? '') === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    <select name="icon_tone" class="form-control">
                                        @foreach(\App\Classes\NoxhTone::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon_tone', ($question->icon_tone) ?? \App\Classes\NoxhTone::MAC_DINH) === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        {{-- Ten NGAN in tren thanh 8 buoc o dau trang. --}}
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Tên bước</label>
                                    <input type="text" name="step_label" value="{{ old('step_label', ($question->step_label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Chữ dưới vòng tròn ở thanh bước. Ví dụ: Đối tượng</small>
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
