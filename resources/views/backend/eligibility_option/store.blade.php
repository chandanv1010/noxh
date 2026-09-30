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
                    {{-- Chi dung voi cau hoi dat bo cuc "Chia theo tinh huong"
                         (ban ve noxh_image/w-2.jpg): dap an nam trong tam nao. --}}
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc tình huống</label>
                                <select name="eligibility_option_group_id" class="form-control">
                                    <option value="">— Không thuộc tình huống nào —</option>
                                    @foreach(\App\Models\EligibilityOptionGroup::with('question')->orderBy('eligibility_question_id')->orderBy('order')->get() as $tam)
                                        <option value="{{ $tam->id }}" {{ (int) old('eligibility_option_group_id', ($option->eligibility_option_group_id) ?? 0) === $tam->id ? 'selected' : '' }}>
                                            {{ $tam->label }} — {{ \Illuminate\Support\Str::limit($tam->question->question ?? '', 50) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Để trống nếu câu hỏi dùng bố cục lưới ô đáp án.</small>
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
                                <label class="control-label text-left mb10">Dừng sớm</label>
                                <label class="control-label text-left">
                                    <input type="hidden" name="stop_flow" value="0">
                                    <input type="checkbox" name="stop_flow" value="1" {{ old('stop_flow', ($option->stop_flow) ?? 0) ? 'checked' : '' }}>
                                    Chọn đáp án này thì bỏ qua các bước còn lại
                                </label>
                                <small class="text-muted">Dùng khi đáp án đã đủ kết luận không đạt. Ví dụ: "Đã từng được hỗ trợ".</small>
                                <label class="control-label text-left mt10">
                                    <input type="hidden" name="needs_distance" value="0">
                                    <input type="checkbox" name="needs_distance" value="1" {{ old('needs_distance', ($option->needs_distance) ?? 0) ? 'checked' : '' }}>
                                    Xét theo khoảng cách
                                </label>
                                <small class="text-muted">Đáp án vẫn đạt nếu nhà cách nơi làm việc đủ xa và nơi làm việc gần dự án. Ví dụ: "Có nhà ở".</small>
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
                    {{-- Hinh tron ben trai o dap an o trang Kiem tra dieu kien
                         (ban ve noxh_image/w-1.jpg).

                         Uu tien ANH: ban ve dung tranh minh hoa nhieu mau. Khong
                         co anh thi lui ve hinh net trong vong tron mau ben duoi,
                         khong co ca hai thi o dap an chi co chu. --}}
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left mb10">Ảnh của đáp án</label>
                                <span class="image img-cover image-target"
                                      style="height:120px;padding:12px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                    <img src="{{ old('image', ($option->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                </span>
                                <input type="hidden" name="image" value="{{ old('image', ($option->image) ?? '') }}">
                                <small class="text-muted">Bấm vào ô trên để chọn ảnh. Ảnh tròn, nền trong suốt.</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
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
                        <div class="col-lg-4">
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
