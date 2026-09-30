{{--
    Buoc cuoi: o nhap ho ten - so dien thoai (ban ve noxh_image/w-5.jpg).

    Khong hoi gi nua, chi xin thong tin de tra ket qua. O tich dong y bi khoa
    nut cho toi khi nguoi dung tich - JS chi de tien tay, phia may chu van
    luu consent = true khi nhan bai.
--}}
<div class="nx-wz-tin">
    <div class="nx-field">
        <label for="nx-wz-ten">
            {{ $intro['wizard_contact_name'] ?? 'Họ và tên' }} <i>*</i>
        </label>
        <span class="nx-wz-tin__o">
            @include('frontend.noxh.component.icon', ['name' => 'user', 'size' => 20])
            <input type="text" id="nx-wz-ten" name="name" value="{{ old('name') }}"
                   placeholder="{{ $intro['wizard_contact_name_hint'] ?? '' }}" required>
        </span>
    </div>

    <div class="nx-field">
        <label for="nx-wz-sdt">
            {{ $intro['wizard_contact_phone'] ?? 'Số điện thoại' }} <i>*</i>
        </label>
        <span class="nx-wz-tin__o">
            @include('frontend.noxh.component.icon', ['name' => 'phone', 'size' => 20])
            <input type="tel" id="nx-wz-sdt" name="phone" value="{{ old('phone') }}"
                   placeholder="{{ $intro['wizard_contact_phone_hint'] ?? '' }}" required>
        </span>
    </div>

    @if(!empty($intro['wizard_contact_note']))
        <p class="nx-wz-tin__cam">
            @include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 17])
            {{ $intro['wizard_contact_note'] }}
        </p>
    @endif

    @if(!empty($intro['wizard_consent_text']))
        <label class="nx-wz-tin__y">
            <input type="checkbox" name="consent" value="1" id="nx-wz-dong-y" checked>
            <span>
                {{ $intro['wizard_consent_text'] }}
                @if(!empty($intro['wizard_consent_link_text']))
                    <a href="{{ url($intro['wizard_consent_link'] ?? '/chinh-sach-bao-mat') }}">
                        {{ $intro['wizard_consent_link_text'] }}
                    </a>
                @endif
            </span>
        </label>
    @endif
</div>
