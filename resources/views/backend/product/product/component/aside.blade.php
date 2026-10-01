<div class="ibox w">
    <div class="ibox-title">
        <h5>{{ __('messages.parent') }}</h5>
    </div>
    <div class="ibox-content">
        <div class="row mb15">
            <div class="col-lg-12">
                <div class="form-row">
                    <select name="product_catalogue_id" class="form-control setupSelect2" id="">
                        @foreach ($dropdown as $key => $val)
                            <option
                                {{ $key == old('product_catalogue_id', isset($product->product_catalogue_id) ? $product->product_catalogue_id : '') ? 'selected' : '' }}
                                value="{{ $key }}">{{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        @php
            $catalogue = [];
            if (isset($product)) {
                foreach ($product->product_catalogues as $key => $val) {
                    $catalogue[] = $val->id;
                }
            }
        @endphp
        <div class="row">
            <div class="col-lg-12">
                <div class="form-row">
                    <label class="control-label">{{ __('messages.subparent') }}</label>
                    <select multiple name="catalogue[]" class="form-control setupSelect2" id="">
                        @foreach ($dropdown as $key => $val)
                            <option @if (is_array(old('catalogue', isset($catalogue) && count($catalogue) ? $catalogue : [])) &&
                                    isset($product->product_catalogue_id) &&
                                    $key !== $product->product_catalogue_id &&
                                    in_array($key, old('catalogue', isset($catalogue) ? $catalogue : []))) selected @endif value="{{ $key }}">
                                {{ $val }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>
{{--
    Da bo hai hop "Thong tin san pham" (ma, xuat xu, gia, ton kho, bao hanh,
    ma nhung video) va "Cau hinh Uu dai" khoi man hinh du an.

    Day la o cua ma nguon ban hang goc. Du an nha o xa hoi dung cac o rieng o
    khoi "Thong tin du an NOXH" ben duoi (gia tu - den, dien tich, so can,
    chu dau tu, video du an...), hai bo o de canh nhau chi lam nguoi nhap lan.

    Gia tri cu cua chung van duoc giu nguyen - xem
    component/o-an.blade.php.
--}}

@include('backend.product.product.component.nhan-vien')
@include('backend.product.product.component.du-an-tuong-tu')

@include('backend.dashboard.component.publish', ['model' => $product ?? null, 'hideImage' => false])

@if (!empty($product->qrcode))
    <div class="ibox w">
        <div class="ibox-title">
            <h5>Mã QRCODE</h5>
        </div>
        <div class="ibox-content qrcode">
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-row">
                        {!! $product->qrcode !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
