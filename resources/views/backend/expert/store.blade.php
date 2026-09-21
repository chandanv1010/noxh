@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('expert.store')
        : route('expert.update', $expert->id);
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
                                <label class="control-label text-left">Họ và tên <span class="text-danger">(*)</span></label>
                                    <input type="text" name="name" value="{{ old('name', ($expert->name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-row">
                                <label class="control-label text-left">Chức danh</label>
                                    <input type="text" name="title" value="{{ old('title', ($expert->title) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Cố vấn pháp lý</small>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Điện thoại</label>
                                    <input type="text" name="phone" value="{{ old('phone', ($expert->phone) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Zalo</label>
                                    <input type="text" name="zalo" value="{{ old('zalo', ($expert->zalo) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Email</label>
                                    <input type="text" name="email" value="{{ old('email', ($expert->email) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Giới thiệu</label>
                                    <textarea name="description" class="ck-editor" id="ck_description" data-height="220">{{ old('description', ($expert->description) ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Cam kết</label>
                                    <textarea name="commitments" rows="3" class="form-control">{{ old('commitments', ($expert->commitments) ?? '') }}</textarea>
                                    <small class="text-muted">Mỗi dòng một cam kết</small>
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
                                    <label class="control-label text-left mb10">Ảnh đại diện</label>
                                    <span class="image img-cover image-target"
                                          style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($expert->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($expert->image) ?? '') }}">
                                    <small class="text-muted">Bấm vào ô trên để chọn ảnh.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">
                                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', ($expert->is_default) ?? 0) ? 'checked' : '' }}>
                                        Chuyên gia mặc định
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($expert->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($expert->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($expert->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
