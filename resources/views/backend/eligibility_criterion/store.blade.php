@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('eligibility.criterion.store')
        : route('eligibility.criterion.update', $criterion->id);
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
                                    <label class="control-label text-left">Tên tiêu chí <span class="text-danger">(*)</span></label>
                                    <input type="text" name="label" value="{{ old('label', ($criterion->label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Điều kiện thu nhập</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Lấy kết luận từ <span class="text-danger">(*)</span></label>
                                    <select name="source" class="form-control">
                                        @foreach(\App\Models\EligibilityCriterion::NGUON as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('source', ($criterion->source) ?? 'question') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Bước tương ứng</label>
                                    <select name="eligibility_question_id" class="form-control">
                                        <option value="">— Không gắn bước nào —</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('eligibility_question_id', ($criterion->eligibility_question_id) ?? 0) === $cha->id ? 'selected' : '' }}>
                                                {{ $cha->order + 1 }}. {{ $cha->tenBuoc() }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Chỉ dùng khi lấy kết luận từ câu trả lời của một bước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Dòng mô tả khi <strong>Phù hợp</strong></label>
                                    <input type="text" name="pass_text" value="{{ old('pass_text', ($criterion->pass_text) ?? '') }}" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Dòng mô tả khi <strong>Cần xác minh</strong></label>
                                    <input type="text" name="unclear_text" value="{{ old('unclear_text', ($criterion->unclear_text) ?? '') }}" class="form-control" autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Dòng mô tả khi <strong>Chưa đáp ứng</strong></label>
                                    <input type="text" name="fail_text" value="{{ old('fail_text', ($criterion->fail_text) ?? '') }}" class="form-control" autocomplete="off">
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
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($criterion->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($criterion->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($criterion->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
