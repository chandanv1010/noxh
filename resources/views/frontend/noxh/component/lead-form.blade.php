{{--
    Form de lai thong tin. Dung o cot phai trang chi tiet du an va trang tu van.

    $tieuDe, $moTa, $nguon, $duAnId (tuy chon)
--}}
<div class="nx-panel">
    <h2 class="nx-panel__title">{{ $tieuDe ?? 'Bạn quan tâm dự án?' }}</h2>

    @if(!empty($moTa))
        <p class="nx__subheading" style="margin-bottom:14px">{{ $moTa }}</p>
    @endif

    @include('frontend.noxh.component.alert')

    <form method="POST" action="{{ route('noxh.lead.store') }}">
        @csrf
        <input type="hidden" name="source" value="{{ $nguon ?? 'website' }}">
        @if(!empty($duAnId))
            <input type="hidden" name="product_id" value="{{ $duAnId }}">
        @endif

        <div class="nx-field">
            <label for="lead-name">Họ và tên <span style="color:#dc2626">*</span></label>
            <input type="text" id="lead-name" name="name" value="{{ old('name') }}" placeholder="Nhập họ và tên" required>
        </div>

        <div class="nx-field">
            <label for="lead-phone">Số điện thoại <span style="color:#dc2626">*</span></label>
            <input type="tel" id="lead-phone" name="phone" value="{{ old('phone') }}" placeholder="Nhập số điện thoại" required>
        </div>

        <div class="nx-field">
            <label for="lead-interest">Nhu cầu quan tâm</label>
            <input type="text" id="lead-interest" name="interest" value="{{ old('interest') }}"
                   placeholder="Ví dụ: căn 2PN, tầng trung">
        </div>

        <button type="submit" class="nx-btn nx-btn--block">ĐĂNG KÝ TƯ VẤN NGAY</button>
    </form>

    <ul class="nx-ticks" style="margin-top:16px">
        <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Tư vấn miễn phí 24/7</li>
        <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Hỗ trợ kiểm tra điều kiện</li>
        <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Đồng hành đến khi có nhà</li>
    </ul>
</div>
