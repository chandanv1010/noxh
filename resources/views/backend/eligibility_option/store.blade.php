@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('eligibility.option.store')
        : route('eligibility.option.update', $option->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc câu hỏi <span class="text-danger">(*)</span></label>
                                    <select name="eligibility_question_id" class="form-control">
                                        <option value="">[Chọn]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('eligibility_question_id', ($option->eligibility_question_id) ?? request('eligibility_question_id')) === $cha->id ? 'selected' : '' }}>{{ $cha->name ?? $cha->question }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Nội dung đáp án <span class="text-danger">(*)</span></label>
                                    <input type="text" name="label" value="{{ old('label', ($option->label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Chữ người dùng nhìn thấy</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Giá trị lưu <span class="text-danger">(*)</span></label>
                                    <input type="text" name="value" value="{{ old('value', ($option->value) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Không dấu, không khoảng trắng. Ví dụ: chua_so_huu</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Kết luận <span class="text-danger">(*)</span></label>
                                    <select name="verdict" class="form-control">
                                        @foreach(\App\Models\EligibilityOption::KET_LUAN as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('verdict', ($option->verdict) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Quyết định ô Đạt / Cần kiểm tra ở bảng kết quả</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Điểm</label>
                                    <input type="number" name="score" value="{{ old('score', ($option->score) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Cộng vào tổng điểm khi người dùng chọn đáp án này</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($option->order) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Ghi chú</label>
                                    <textarea name="note" rows="3" class="form-control">{{ old('note', ($option->note) ?? '') }}</textarea>
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
