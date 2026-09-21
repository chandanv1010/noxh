@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('project.document.store')
        : route('project.document.update', $document->id);
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
                                <label class="control-label text-left">Thuộc dự án <span class="text-danger">(*)</span></label>
                                    <select name="product_id" class="form-control setupSelect2">
                                        <option value="">[Chọn dự án]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('product_id', ($document->product_id) ?? request('product_id')) === (int) $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Tên hồ sơ <span class="text-danger">(*)</span></label>
                                    <input type="text" name="title" value="{{ old('title', ($document->title) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Quyết định chủ trương đầu tư</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Số hiệu</label>
                                    <input type="text" name="doc_number" value="{{ old('doc_number', ($document->doc_number) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: 123/QĐ-UBND</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Ngày ban hành</label>
                                    <input type="date" name="issued_date" value="{{ old('issued_date', isset($document->issued_date) && $document->issued_date ? \Illuminate\Support\Carbon::parse($document->issued_date)->format('Y-m-d') : '') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Cơ quan ban hành</label>
                                    <input type="text" name="issuer" value="{{ old('issuer', ($document->issuer) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($document->order) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Ghi chú</label>
                                    <textarea name="description" rows="3" class="form-control">{{ old('description', ($document->description) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">File hồ sơ</label>
                                    <input type="text" name="file" value="{{ old('file', ($document->file) ?? '') }}"
                                           class="form-control upload-image" data-type="Files" autocomplete="off"
                                           placeholder="Bấm để chọn file">
                                    <small class="text-muted">Bấm vào ô để mở kho file. Nên dùng PDF.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($document->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($document->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
