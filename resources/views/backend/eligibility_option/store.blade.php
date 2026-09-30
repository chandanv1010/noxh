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
                    {{-- Hinh tron pastel ben trai o dap an o trang Kiem tra dieu kien
                         (ban ve noxh_image/w-1.jpg). Bo trong thi o dap an chi co chu. --}}
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Hình của đáp án</label>
                                    <select name="icon" class="form-control">
                                        @foreach(\App\Classes\NoxhIcon::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon', ($option->icon) ?? '') === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Hiện trong hình tròn bên trái ô đáp án</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Màu hình tròn</label>
                                    <select name="icon_tone" class="form-control">
                                        @foreach(\App\Classes\NoxhTone::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon_tone', ($option->icon_tone) ?? \App\Classes\NoxhTone::MAC_DINH) === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Ghi chú</label>
                                    <textarea name="note" rows="3" class="form-control">{{ old('note', ($option->note) ?? '') }}</textarea>
                                    <small class="text-muted">Dòng chữ nhỏ màu xám dưới tên đáp án. Ví dụ: (có hiệu lực từ 01/7/2026)</small>
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
