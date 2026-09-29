@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('project.unit.store')
        : route('project.unit.update', $unit->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">
                    <div class="row mb15">
                        <div class="col-lg-8">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc dự án <span class="text-danger">(*)</span></label>
                                <select name="product_id" class="form-control setupSelect2">
                                    <option value="">[Chọn dự án]</option>
                                    @foreach($danhSachCha as $cha)
                                        <option value="{{ $cha->id }}" {{ (int) old('product_id', ($unit->product_id) ?? request('product_id')) === (int) $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                <input type="number" name="order" value="{{ old('order', ($unit->order) ?? 0) }}" class="form-control" min="0">
                                <small class="text-muted">Số nhỏ đứng trước</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Tên loại căn hộ <span class="text-danger">(*)</span></label>
                                <input type="text" name="name" value="{{ old('name', ($unit->name) ?? '') }}" class="form-control" autocomplete="off">
                                <small class="text-muted">Ví dụ: Căn 1PN - 1WC</small>
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Diện tích từ (m²)</label>
                                <input type="number" step="0.01" min="0" name="area_from" value="{{ old('area_from', ($unit->area_from) ?? '') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Diện tích đến (m²)</label>
                                <input type="number" step="0.01" min="0" name="area_to" value="{{ old('area_to', ($unit->area_to) ?? '') }}" class="form-control">
                                <small class="text-muted">Bỏ trống nếu chỉ có một con số</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Giá từ</label>
                                <input type="number" step="0.001" min="0" name="price_from" value="{{ old('price_from', ($unit->price_from) ?? '') }}" class="form-control">
                                <small class="text-muted">Giá CẢ CĂN, không phải giá mỗi m²</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Giá đến</label>
                                <input type="number" step="0.001" min="0" name="price_to" value="{{ old('price_to', ($unit->price_to) ?? '') }}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Đơn vị giá</label>
                                <input type="text" name="price_unit" value="{{ old('price_unit', ($unit->price_unit) ?? 'tỷ') }}" class="form-control" autocomplete="off">
                                <small class="text-muted">Mặc định: tỷ</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Đường dẫn "Xem chi tiết"</label>
                                <input type="text" name="url" value="{{ old('url', ($unit->url) ?? '') }}" class="form-control" autocomplete="off">
                                <small class="text-muted">Bỏ trống thì nút mở form đăng ký tư vấn của dự án</small>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-row">
                                <label class="control-label text-left">Hiển thị</label>
                                <select name="publish" class="form-control">
                                    <option value="2" {{ (int) old('publish', ($unit->publish) ?? 2) === 2 ? 'selected' : '' }}>Hiển thị</option>
                                    <option value="1" {{ (int) old('publish', ($unit->publish) ?? 2) === 1 ? 'selected' : '' }}>Ẩn</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Các gạch đầu dòng</label>
                                <textarea name="bullets" rows="4" class="form-control">{{ old('bullets', ($unit->bullets) ?? '') }}</textarea>
                                <small class="text-muted">
                                    Mỗi dòng một ý, ví dụ:<br>
                                    Phù hợp người độc thân<br>
                                    Tối ưu công năng
                                </small>
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
                                    <label class="control-label text-left mb10">Ảnh mặt bằng căn</label>
                                    <span class="image img-cover image-target"
                                          style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($unit->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($unit->image) ?? '') }}">
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
