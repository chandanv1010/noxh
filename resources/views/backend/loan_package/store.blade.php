@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('loan.package.store')
        : route('loan.package.update', $package->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Tên ngân hàng <span class="text-danger">(*)</span></label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name', ($package->bank_name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Tên gói vay</label>
                                    <input type="text" name="package_name" value="{{ old('package_name', ($package->package_name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Lãi suất ưu đãi (%/năm)</label>
                                    <input type="number" step="0.01" name="preferential_rate" value="{{ old('preferential_rate', ($package->preferential_rate) ?? '') }}" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Số tháng ưu đãi</label>
                                    <input type="number" name="preferential_months" value="{{ old('preferential_months', ($package->preferential_months) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Hết số tháng này thì áp lãi suất sau ưu đãi</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Lãi suất sau ưu đãi (%/năm)</label>
                                    <input type="number" step="0.01" name="standard_rate" value="{{ old('standard_rate', ($package->standard_rate) ?? '') }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Vay tối đa (% giá trị căn)</label>
                                    <input type="number" step="0.01" name="max_loan_ratio" value="{{ old('max_loan_ratio', ($package->max_loan_ratio) ?? '') }}" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thời hạn tối đa (năm)</label>
                                    <input type="number" name="max_term_years" value="{{ old('max_term_years', ($package->max_term_years) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Phí trả trước hạn (%)</label>
                                    <input type="number" step="0.01" name="prepayment_fee" value="{{ old('prepayment_fee', ($package->prepayment_fee) ?? '') }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Hotline ngân hàng</label>
                                    <input type="text" name="hotline" value="{{ old('hotline', ($package->hotline) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Áp dụng từ ngày</label>
                                    <input type="date" name="effective_from" value="{{ old('effective_from', isset($package->effective_from) && $package->effective_from ? $package->effective_from->format('Y-m-d') : '') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Điều kiện vay</label>
                                    <textarea name="conditions" class="ck-editor" id="ck_conditions" data-height="220">{{ old('conditions', ($package->conditions) ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Ghi chú</label>
                                    <textarea name="note" rows="3" class="form-control">{{ old('note', ($package->note) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">Logo ngân hàng</label>
                                    <span class="image img-cover image-target"
                                          style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('bank_logo', ($package->bank_logo) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="bank_logo" value="{{ old('bank_logo', ($package->bank_logo) ?? '') }}">
                                    <small class="text-muted">Bấm vào ô trên để chọn ảnh.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', ($package->is_featured) ?? 0) ? 'checked' : '' }}>
                                        Gói nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($package->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($package->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($package->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
