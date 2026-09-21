@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('dossier.item.store')
        : route('dossier.item.update', $item->id);
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
                                <label class="control-label text-left">Thuộc bộ hồ sơ <span class="text-danger">(*)</span></label>
                                    <select name="dossier_set_id" class="form-control">
                                        <option value="">[Chọn]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('dossier_set_id', ($item->dossier_set_id) ?? request('dossier_set_id')) === $cha->id ? 'selected' : '' }}>{{ $cha->name ?? $cha->question }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Tên giấy tờ <span class="text-danger">(*)</span></label>
                                    <input type="text" name="title" value="{{ old('title', ($item->title) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Nơi cấp</label>
                                    <input type="text" name="issued_by" value="{{ old('issued_by', ($item->issued_by) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Số bản cần nộp</label>
                                    <input type="number" name="copies" value="{{ old('copies', ($item->copies) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Tên mẫu đơn</label>
                                    <input type="text" name="template_name" value="{{ old('template_name', ($item->template_name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Hướng dẫn</label>
                                    <textarea name="description" class="ck-editor" id="ck_description" data-height="220">{{ old('description', ($item->description) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">File mẫu đơn</label>
                                    <input type="text" name="template_file" value="{{ old('template_file', ($item->template_file) ?? '') }}"
                                           class="form-control upload-image" data-type="Files" autocomplete="off"
                                           placeholder="Bấm để chọn file">
                                    <small class="text-muted">Bấm vào ô để mở kho file. Nên dùng PDF.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="is_required" value="1" {{ old('is_required', ($item->is_required) ?? 0) ? 'checked' : '' }}>
                                        Bắt buộc
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($item->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($item->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($item->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
