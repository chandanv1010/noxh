@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('legal.document.store')
        : route('legal.document.update', $document->id);
@endphp

{{-- enctype bat buoc phai co, khong thi o chon file gui len chuoi rong. --}}
<form action="{{ $url }}" method="post" class="box" enctype="multipart/form-data">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Tên văn bản <span class="text-danger">(*)</span></label>
                                    <input type="text" name="title" value="{{ old('title', ($document->title) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Số hiệu</label>
                                    <input type="text" name="doc_number" value="{{ old('doc_number', ($document->doc_number) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: 100/2024/NĐ-CP</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Loại văn bản <span class="text-danger">(*)</span></label>
                                    <select name="doc_type" class="form-control">
                                        @foreach(\App\Models\LegalDocument::LOAI as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('doc_type', ($document->doc_type) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Cơ quan ban hành</label>
                                    <input type="text" name="issuer" value="{{ old('issuer', ($document->issuer) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Ngày ban hành</label>
                                    <input type="date" name="issued_date" value="{{ old('issued_date', isset($document->issued_date) && $document->issued_date ? $document->issued_date->format('Y-m-d') : '') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Ngày có hiệu lực</label>
                                    <input type="date" name="effective_date" value="{{ old('effective_date', isset($document->effective_date) && $document->effective_date ? $document->effective_date->format('Y-m-d') : '') }}" class="form-control">
                                    <small class="text-muted">Danh sách ngoài website sắp xếp theo ngày này</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Tóm tắt nội dung</label>
                                    <textarea name="summary" class="ck-editor" id="ck_summary" data-height="220">{{ old('summary', ($document->summary) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">File văn bản</label>

                                    @if(!empty($document->file))
                                        <div class="alert alert-info" style="padding:8px 10px;margin-bottom:10px">
                                            <i class="fa fa-file-o"></i>
                                            <a href="{{ $document->file }}" target="_blank">{{ basename($document->file) }}</a>
                                            @if($document->file_size)
                                                <span class="text-muted">({{ dung_luong($document->file_size) }})</span>
                                            @endif
                                        </div>
                                    @endif

                                    <input type="file" name="tep_tai_len" class="form-control"
                                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.rtf,.txt,.csv,.zip,.rar">
                                    <small class="text-muted">
                                        PDF, Word, Excel, PowerPoint, TXT, CSV hoặc file nén. Tối đa 20MB.
                                        @if(!empty($document->file)) Chọn file mới để thay file đang có. @endif
                                    </small>

                                    <div class="form-row mt15">
                                        <label class="control-label text-left mb10">Hoặc dán đường dẫn có sẵn</label>
                                        <input type="text" name="file" value="{{ old('file', ($document->file) ?? '') }}"
                                               class="form-control" autocomplete="off"
                                               placeholder="https://...">
                                        <small class="text-muted">Dùng khi file đã nằm ở nơi khác. Tải file lên sẽ ghi đè ô này.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', ($document->is_featured) ?? 0) ? 'checked' : '' }}>
                                        Văn bản nổi bật
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($document->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
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
