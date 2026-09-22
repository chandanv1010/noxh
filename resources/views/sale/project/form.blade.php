{{--
    Form du an cua nhan vien kinh doanh.

    Dung lai dung nhung partial ma trang quan tri dung, nen mot lan sua o
    backend la ca hai noi cung doi theo. Bo han cac khoi khong lien quan toi
    du an nha o xa hoi: bien the, gia ban, ton kho, bao hanh, ma QR.
--}}
@php
    $duong = $cachLam === 'create'
        ? route('sale.project.store')
        : route('sale.project.update', $product->id);
@endphp

<form action="{{ $duong }}" method="post" class="box">
    @csrf

    <div class="row">
        <div class="col-lg-9">
            <div class="ibox">
                <div class="ibox-title"><h5>Thông tin chung</h5></div>
                <div class="ibox-content">
                    @include('backend.product.product.component.content', ['model' => $product])
                </div>
            </div>

            @include('backend.product.product.component.noxh')
            @include('backend.dashboard.component.album', ['model' => $product])
            @include('backend.dashboard.component.seo', ['model' => $product])
        </div>

        <div class="col-lg-3">
            <div class="ibox">
                <div class="ibox-title"><h5>Nhóm dự án</h5></div>
                <div class="ibox-content">
                    @php
                        $nhomDaChon = [];
                        if ($product) {
                            foreach ($product->product_catalogues as $nhom) {
                                $nhomDaChon[] = $nhom->id;
                            }
                        }
                    @endphp

                    <div class="form-row mb15">
                        <label class="control-label">Nhóm chính</label>
                        <select name="product_catalogue_id" class="form-control setupSelect2">
                            @foreach($dropdown as $ma => $ten)
                                <option value="{{ $ma }}"
                                    {{ (int) old('product_catalogue_id', $product->product_catalogue_id ?? 0) === (int) $ma ? 'selected' : '' }}>
                                    {{ $ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <label class="control-label">Nhóm phụ</label>
                        <select multiple name="catalogue[]" class="form-control setupSelect2">
                            @foreach($dropdown as $ma => $ten)
                                <option value="{{ $ma }}"
                                    {{ in_array($ma, old('catalogue', $nhomDaChon)) ? 'selected' : '' }}>
                                    {{ $ten }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            @include('backend.dashboard.component.publish', ['model' => $product, 'hideImage' => false])

            {{-- Chi hien khi quan tri bat che do duyet, va chi o man hinh them moi:
                 du an duoc giao thi khong bao gio roi vao trang thai cho duyet. --}}
            @if($cachLam === 'create' && cai_dat('sale_project_approval', 'off') === 'on')
                <div class="ibox">
                    <div class="ibox-content">
                        <p class="text-muted" style="font-size:13px;margin:0">
                            <i class="fa fa-info-circle"></i>
                            Dự án bạn tự thêm sẽ ở trạng thái <strong>chờ duyệt</strong>
                            cho tới khi quản trị bật hiển thị.
                        </p>
                    </div>
                </div>
            @endif

            {{--
                "Dự án nổi bật" là quyết định biên tập của quản trị, không mở cho
                nhân viên. Nhưng vẫn phải gửi lên giá trị hiện tại: service đọc
                $request->boolean('is_featured') ở mọi lần lưu, không gửi thì mỗi
                lần nhân viên bấm lưu là dự án lặng lẽ rớt khỏi trang chủ.
            --}}
            <input type="hidden" name="is_featured" value="{{ (int) ($product->is_featured ?? 0) }}">
        </div>
    </div>

    <div class="text-right mb15 fixed-bottom">
        <a href="{{ route('sale.project.index') }}" class="btn btn-white">Quay lại</a>
        <button class="btn btn-primary" type="submit">Lưu dự án</button>
    </div>
</form>
