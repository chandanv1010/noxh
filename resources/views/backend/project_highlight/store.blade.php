@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('project.highlight.store')
        : route('project.highlight.update', $highlight->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-9">
                <div class="ibox">
                    <div class="ibox-content">

                    <div class="alert alert-info">
                        Màn hình này nhập cho <strong>hai khối</strong> của trang chi tiết dự án:
                        bốn ô điểm nhấn trong thẻ giá ở đầu trang, và các ô của khối "Tiện ích".
                        Dự án nào <strong>không khai ô điểm nhấn nào</strong> thì trang tự lấy bốn ô mặc định
                        trong Cấu hình chung → nhóm <em>Trang chi tiết dự án - phần chính</em>.
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-8">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc dự án <span class="text-danger">(*)</span></label>
                                <select name="product_id" class="form-control setupSelect2">
                                    <option value="">[Chọn dự án]</option>
                                    @foreach($danhSachCha as $cha)
                                        <option value="{{ $cha->id }}" {{ (int) old('product_id', ($highlight->product_id) ?? request('product_id')) === (int) $cha->id ? 'selected' : '' }}>{{ $cha->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Thứ tự</label>
                                <input type="number" name="order" value="{{ old('order', ($highlight->order) ?? 0) }}" class="form-control" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-12">
                            <div class="form-row">
                                <label class="control-label text-left">Thuộc khối <span class="text-danger">(*)</span></label>
                                <select name="group" class="form-control">
                                    @foreach(\App\Models\ProjectHighlight::NHOM as $ma => $ten)
                                        <option value="{{ $ma }}" {{ old('group', ($highlight->group) ?? request('group', 'price')) === $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row mb15">
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Hình minh họa</label>
                                <select name="icon" class="form-control">
                                    @foreach($danhSachIcon as $ma => $ten)
                                        <option value="{{ $ma }}" {{ old('icon', ($highlight->icon) ?? '') === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Dòng trên <span class="text-danger">(*)</span></label>
                                <input type="text" name="title" value="{{ old('title', ($highlight->title) ?? '') }}" class="form-control" maxlength="40" autocomplete="off">
                                <small class="text-muted">Ví dụ: Vị trí</small>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-row">
                                <label class="control-label text-left">Dòng dưới</label>
                                <input type="text" name="subtitle" value="{{ old('subtitle', ($highlight->subtitle) ?? '') }}" class="form-control" maxlength="40" autocomplete="off">
                                <small class="text-muted">Ví dụ: trung tâm</small>
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
