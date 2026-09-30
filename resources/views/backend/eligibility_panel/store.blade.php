@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('eligibility.panel.store')
        : route('eligibility.panel.update', $panel->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-8">
                                <div class="form-row">
                                    <label class="control-label text-left">Thuộc bước <span class="text-danger">(*)</span></label>
                                    <select name="eligibility_question_id" class="form-control">
                                        <option value="">[Chọn]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('eligibility_question_id', ($panel->eligibility_question_id) ?? request('eligibility_question_id')) === $cha->id ? 'selected' : '' }}>
                                                {{ $cha->order + 1 }}. {{ $cha->tenBuoc() }} — {{ \Illuminate\Support\Str::limit($cha->question, 50) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($panel->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ nằm trên.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Tiêu đề của tấm</label>
                                    <input type="text" name="heading" value="{{ old('heading', ($panel->heading) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Vì sao cần thông tin này?</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Đoạn chữ</label>
                                    <textarea name="body" rows="5" class="form-control">{{ old('body', ($panel->body) ?? '') }}</textarea>
                                    <small class="text-muted">Để trống nếu tấm chỉ có danh sách gạch đầu dòng.</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Danh sách gạch đầu dòng</label>
                                    <textarea name="bullets" rows="5" class="form-control">{{ old('bullets', ($panel->bullets) ?? '') }}</textarea>
                                    <small class="text-muted">Mỗi dòng một ý, trang ngoài vẽ thành dấu tích.</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Tranh của tấm</label>
                                    <span class="image img-cover image-target"
                                          style="height:130px;padding:12px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($panel->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($panel->image) ?? '') }}">
                                    <small class="text-muted">Tấm chỉ có tranh thì bỏ trống tiêu đề và chữ.</small>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Hình trước tiêu đề</label>
                                    <select name="icon" class="form-control">
                                        @foreach(\App\Classes\NoxhIcon::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon', ($panel->icon) ?? '') === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Màu nền của tấm</label>
                                    <select name="tone" class="form-control">
                                        @foreach(\App\Classes\NoxhTone::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('tone', ($panel->tone) ?? \App\Classes\NoxhTone::MAC_DINH) === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
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
