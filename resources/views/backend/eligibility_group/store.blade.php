@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo'][$config['method'] === 'create' ? 'create' : 'edit']['title']])
@include('backend.dashboard.component.formError')

@php
    $url = ($config['method'] == 'create')
        ? route('eligibility.group.store')
        : route('eligibility.group.update', $group->id);
@endphp

<form action="{{ $url }}" method="post" class="box">
    @csrf
    <div class="wrapper wrapper-content animated fadeInRight">
        <div class="row">
            <div class="col-lg-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row mb15">
                            <div class="col-lg-12">
                                <div class="form-row">
                                    <label class="control-label text-left">Thuộc câu hỏi <span class="text-danger">(*)</span></label>
                                    <select name="eligibility_question_id" class="form-control">
                                        <option value="">[Chọn]</option>
                                        @foreach($danhSachCha as $cha)
                                            <option value="{{ $cha->id }}" {{ (int) old('eligibility_question_id', ($group->eligibility_question_id) ?? request('eligibility_question_id')) === $cha->id ? 'selected' : '' }}>{{ $cha->question }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Chỉ có tác dụng với câu hỏi đặt bố cục "Chia theo tình huống".</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Tên tình huống <span class="text-danger">(*)</span></label>
                                    <input type="text" name="label" value="{{ old('label', ($group->label) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: Độc thân</small>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-row">
                                    <label class="control-label text-left">Dòng ghi chú</label>
                                    <input type="text" name="note" value="{{ old('note', ($group->note) ?? '') }}" class="form-control" autocomplete="off">
                                    <small class="text-muted">Ví dụ: (chưa kết hôn)</small>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left mb10">Tranh của tình huống</label>
                                    <span class="image img-cover image-target"
                                          style="height:120px;padding:12px;text-align:center;border:1px dashed #b8b2b2;display:flex;align-items:center;justify-content:center;cursor:pointer;">
                                        <img src="{{ old('image', ($group->image) ?? '') ?: 'backend/img/image.svg' }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <input type="hidden" name="image" value="{{ old('image', ($group->image) ?? '') }}">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Hình (dùng khi chưa có tranh)</label>
                                    <select name="icon" class="form-control">
                                        @foreach(\App\Classes\NoxhIcon::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('icon', ($group->icon) ?? '') === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Màu nền của tấm</label>
                                    <select name="tone" class="form-control">
                                        @foreach(\App\Classes\NoxhTone::chon() as $ma => $ten)
                                            <option value="{{ $ma }}" {{ (string) old('tone', ($group->tone) ?? \App\Classes\NoxhTone::MAC_DINH) === (string) $ma ? 'selected' : '' }}>{{ $ten }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb15">
                            <div class="col-lg-4">
                                <div class="form-row">
                                    <label class="control-label text-left">Thứ tự</label>
                                    <input type="number" name="order" value="{{ old('order', ($group->order) ?? 0) }}" class="form-control" min="0">
                                    <small class="text-muted">Số nhỏ đứng trước.</small>
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
