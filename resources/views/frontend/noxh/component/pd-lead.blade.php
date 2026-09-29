{{--
    Form "DANG KY NHAN THONG TIN DU AN" o cuoi cot phai trang chi tiet.

    Bon o: ho ten, so dien thoai, nhu cau quan tam, thoi gian du kien mua.
    Hai o chon lay lua chon tu Cau hinh chung (moi dong mot lua chon); de
    trong danh sach thi o do khong hien, form tu rut lai con hai o.

    Tham so: $duAn, $intro
--}}
@php
    $tach = function (?string $van): array {
        $dong = preg_split('/\r\n|\r|\n/', (string) $van) ?: [];

        return array_values(array_filter(array_map('trim', $dong), fn ($d) => $d !== ''));
    };

    $nhuCau = $tach($intro['projectlead_form_interest_options'] ?? '');
    $thoiGian = $tach($intro['projectlead_form_timeline_options'] ?? '');
@endphp

<section class="nx-pd-form" id="dang-ky">
    <h2 class="nx-pd-form__tieude">
        {{ $intro['projectlead_form_heading'] ?? 'ĐĂNG KÝ NHẬN THÔNG TIN DỰ ÁN' }}
    </h2>

    @if(!empty($intro['projectlead_form_note']))
        <p class="nx-pd-form__mo">{{ $intro['projectlead_form_note'] }}</p>
    @endif

    @include('frontend.noxh.component.alert')

    <form method="POST" action="{{ route('noxh.lead.store') }}" class="nx-pd-form__o">
        @csrf
        <input type="hidden" name="source" value="project_detail">
        <input type="hidden" name="product_id" value="{{ $duAn->id }}">
        <input type="hidden" name="province_code" value="{{ $duAn->province_code }}">

        <label class="nx-pd-nhap">
            @include('frontend.noxh.component.icon', ['name' => 'user', 'size' => 18])
            <input type="text" name="name" value="{{ old('name') }}" required maxlength="191"
                   placeholder="{{ $intro['projectlead_form_name'] ?? 'Họ và tên *' }}"
                   aria-label="{{ $intro['projectlead_form_name'] ?? 'Họ và tên' }}" autocomplete="name">
        </label>

        <label class="nx-pd-nhap">
            @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 18])
            <input type="tel" name="phone" value="{{ old('phone') }}" required maxlength="20"
                   placeholder="{{ $intro['projectlead_form_phone'] ?? 'Số điện thoại *' }}"
                   aria-label="{{ $intro['projectlead_form_phone'] ?? 'Số điện thoại' }}" autocomplete="tel">
        </label>

        @if(count($nhuCau))
            <label class="nx-pd-nhap">
                @include('frontend.noxh.component.icon', ['name' => 'door', 'size' => 18])
                <select name="interest" aria-label="{{ $intro['projectlead_form_interest'] ?? 'Nhu cầu quan tâm' }}">
                    <option value="">{{ $intro['projectlead_form_interest'] ?? 'Nhu cầu quan tâm' }}</option>
                    @foreach($nhuCau as $n)
                        <option value="{{ $n }}" {{ old('interest') === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                @include('frontend.noxh.component.icon', ['name' => 'chevron-down', 'size' => 18])
            </label>
        @endif

        @if(count($thoiGian))
            <label class="nx-pd-nhap">
                @include('frontend.noxh.component.icon', ['name' => 'calendar', 'size' => 18])
                <select name="buy_timeline" aria-label="{{ $intro['projectlead_form_timeline'] ?? 'Thời gian dự kiến mua' }}">
                    <option value="">{{ $intro['projectlead_form_timeline'] ?? 'Thời gian dự kiến mua' }}</option>
                    @foreach($thoiGian as $t)
                        <option value="{{ $t }}" {{ old('buy_timeline') === $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                @include('frontend.noxh.component.icon', ['name' => 'chevron-down', 'size' => 18])
            </label>
        @endif

        <button type="submit" class="nx-btn nx-btn--block nx-pd-form__nut">
            {{ $intro['projectlead_form_button'] ?? 'ĐĂNG KÝ NGAY' }}
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 18])
        </button>
    </form>

    @if(!empty($intro['projectlead_form_privacy']))
        <p class="nx-pd-form__bao">
            @include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 15])
            {{ $intro['projectlead_form_privacy'] }}
        </p>
    @endif
</section>
