<div class="ibox w">
    <div class="ibox-title">
        <h5>{{ __('messages.parent') }}</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <span class="text-danger notice" >*{{ __('messages.parentNotice') }}</span>
                    <select name="parent_id" class="form-control setupSelect2" id="">
                        @foreach($dropdown as $key => $val)
                        <option {{ 
                            $key == old('parent_id', (isset($postCatalogue->parent_id)) ? $postCatalogue->parent_id : '') ? 'selected' : '' 
                            }} value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
{{--
    Mau nhan cua chuyen muc. Ngoai trang chu, ten chuyen muc hien phia tren
    moi tin bang chinh mau nay - de trong thi dung mau xanh mac dinh.
--}}
<div class="ibox w">
    <div class="ibox-title">
        <h5>Màu nhãn chuyên mục</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <span class="text-danger notice">
                        *Màu của tên chuyên mục hiển thị phía trên mỗi tin ngoài trang chủ. Để trống thì dùng màu xanh mặc định.
                    </span>
                    <input type="color"
                           name="color"
                           value="{{ old('color', $postCatalogue->color ?? '#1668e3') }}"
                           class="form-control"
                           style="height:38px;padding:4px">
                </div>
            </div>
        </div>
    </div>
</div>

{{--
    Hinh cua chuyen muc, hien o cot "Danh muc tin tuc" ben trai trang Tin tuc.
    Danh sach hinh lay tu App\Classes\NoxhIcon - dung bo hinh ma frontend
    dung, khong go tay ten hinh.
--}}
<div class="ibox w">
    <div class="ibox-title">
        <h5>Hình của chuyên mục</h5>
    </div>
    <div class="ibox-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <span class="text-danger notice">
                        *Hiện bên trái tên chuyên mục ở cột danh mục trang Tin tức.
                    </span>
                    <select name="icon" class="form-control setupSelect2">
                        @foreach(\App\Classes\NoxhIcon::chon() as $ma => $ten)
                            <option value="{{ $ma }}"
                                {{ old('icon', ($postCatalogue->icon) ?? '') === (string) $ma ? 'selected' : '' }}>
                                {{ $ten }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

@include('backend.dashboard.component.publish', ['model' => ($postCatalogue) ?? null, 'hideImage' => false])