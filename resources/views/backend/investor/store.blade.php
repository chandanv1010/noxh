@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('investor.store')
        : route('investor.update', $investor->id);
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
                                <label class="control-label text-left">Tên chủ đầu tư <span class="text-danger">(*)</span></label>
                                    <input type="text" name="name" value="{{ old('name', ($investor->name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Tên viết tắt</label>
                                    <input type="text" name="short_name" value="{{ old('short_name', ($investor->short_name) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Hotline</label>
                                    <input type="text" name="hotline" value="{{ old('hotline', ($investor->hotline) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Email</label>
                                    <input type="text" name="email" value="{{ old('email', ($investor->email) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Website</label>
                                    <input type="text" name="website" value="{{ old('website', ($investor->website) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Địa chỉ</label>
                                    <input type="text" name="address" value="{{ old('address', ($investor->address) ?? '') }}" class="form-control" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Giới thiệu</label>
                                    <textarea name="description" class="ck-editor" id="ck_description" data-height="220">{{ old('description', ($investor->description) ?? '') }}</textarea>
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
                                    <label class="control-label text-left mb10">Logo</label>
                                    <span class="image img-cover image-target"
                                          style="height:150px;padding:16px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('logo', ($investor->logo) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="logo" value="{{ old('logo', ($investor->logo) ?? '') }}">
                                    <small class="text-muted">Bấm vào ô trên để chọn ảnh.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($investor->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Trạng thái hiển thị</label>
                                    <select name="publish" class="form-control">
                                        <option value="2" {{ old('publish', ($investor->publish) ?? 2) == 2 ? 'selected' : '' }}>Hiển thị</option>
                                        <option value="1" {{ old('publish', ($investor->publish) ?? 2) == 1 ? 'selected' : '' }}>Ẩn</option>
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
