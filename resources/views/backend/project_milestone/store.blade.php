@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('project.milestone.store')
        : route('project.milestone.update', $milestone->id);
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
                                            <option value="{{ $cha->id }}" {{ (int) old('product_id', ($milestone->product_id) ?? request('product_id')) === (int) $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Tên mốc <span class="text-danger">(*)</span></label>
                                    <input type="text" name="title" value="{{ old('title', ($milestone->title) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Khởi công xây dựng</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Nhãn thời điểm</label>
                                    <input type="text" name="date_label" value="{{ old('date_label', ($milestone->date_label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Quý II/2026 — dự án thường công bố theo quý</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Ngày để sắp xếp</label>
                                    <input type="date" name="sort_date" value="{{ old('sort_date', isset($milestone->sort_date) && $milestone->sort_date ? \Illuminate\Support\Carbon::parse($milestone->sort_date)->format('Y-m-d') : '') }}" class="form-control">
                                    <small class="text-muted">Chỉ dùng để xếp thứ tự, không hiện ra ngoài</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Trạng thái <span class="text-danger">(*)</span></label>
                                    <select name="status" class="form-control">
                                        @foreach(['pending' => 'Chưa tới', 'doing' => 'Đang làm', 'done' => 'Hoàn thành'] as $ma => $ten)
                                            <option value="{{ $ma }}" {{ old('status', ($milestone->status) ?? '') === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($milestone->order) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Mô tả</label>
                                    <textarea name="description" rows="3" class="form-control">{{ old('description', ($milestone->description) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">Hình ảnh</label>
                                    <span class="image img-cover image-target"
                                          style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($milestone->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($milestone->image) ?? '') }}">
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
