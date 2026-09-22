@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['create']['title']])
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
@php
    $url = ($config['method'] == 'create') ? route('user.catalogue.store') : route('user.catalogue.update', $userCatalogue->id);
@endphp
<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-5">
                <div class="panel-head">
                    <div class="panel-title">Thông tin chung</div>
                    <div class="panel-description">
                        <p>Nhập thông tin chung của nhóm thành viên</p>
                        <p>Lưu ý: Những trường đánh dấu <span class="text-danger">(*)</span> là bắt buộc</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label for="" class="control-label text-left">Tên Nhóm <span class="text-danger">(*)</span></label>
                                    <input 
                                        type="text"
                                        name="name"
                                        value="{{ old('name', ($userCatalogue->name) ?? '' ) }}"
                                        class="form-control"
                                        placeholder=""
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label for="" class="control-label text-left">Ghi chú</label>
                                    <input 
                                        type="text"
                                        name="description"
                                        value="{{ old('description', ($userCatalogue->description) ?? '' ) }}"
                                        class="form-control"
                                        placeholder=""
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                {{-- Co nay quyet dinh nhom co duoc vao bang dieu khien /sale hay
                                     khong, va co hien ra o chon "nhan vien phu trach" cua form du
                                     an hay khong. Ma nguon doc theo co chu khong theo id nhom. --}}
                                <div class="form-row">
                                    <label style="font-weight:normal;cursor:pointer;display:flex;align-items:center;gap:8px">
                                        <input type="checkbox" name="is_sale" value="1"
                                            {{ old('is_sale', ($userCatalogue->is_sale) ?? 0) == 1 ? 'checked' : '' }}>
                                        <strong>Là nhóm nhân viên kinh doanh</strong>
                                    </label>
                                    <small class="text-muted">
                                        Thành viên thuộc nhóm này đăng nhập ở <code>/sale</code> thay vì trang quản trị,
                                        chỉ thấy dự án được giao và bài viết của chính mình.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="text-right mb15">
            <button class="btn btn-primary" type="submit" name="send" value="send">Lưu lại</button>
        </div>
    </div>
</form>
