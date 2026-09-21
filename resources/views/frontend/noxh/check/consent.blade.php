@extends('frontend.noxh.layout')

@section('content')
<div class="nx-consent">
    <span class="nx-consent__icon">
        @include('frontend.noxh.component.icon', ['name' => 'clipboard', 'size' => 36])
    </span>

    <h1 class="nx-consent__title">
        Kiểm tra điều kiện mua
        <span>Nhà ở xã hội</span>
    </h1>

    <p class="nx-consent__description">
        Trả lời một số câu hỏi để kiểm tra sơ bộ khả năng đáp ứng điều kiện mua
        NOXH theo quy định hiện hành.
    </p>

    <div class="nx-consent__time">
        @include('frontend.noxh.component.icon', ['name' => 'clock', 'size' => 16])
        Thời gian thực hiện khoảng <strong>3 – 5 phút</strong>
    </div>

    {{-- Nut bat dau bi khoa cho toi khi nguoi dung tich dong y - JS chi de
         tien tay, phia may chu van luu consent = true khi nhan bai. --}}
    <form method="GET" action="{{ url('/kiem-tra-dieu-kien/cau-hoi') }}">
        <div class="nx-consent__box">
            <div style="display:flex;gap:14px;align-items:flex-start">
                <span style="color:#1668e3;flex-shrink:0">
                    @include('frontend.noxh.component.icon', ['name' => 'lock', 'size' => 28])
                </span>
                <div>
                    <strong style="display:block;margin-bottom:4px">Thông tin của bạn được bảo mật</strong>
                    <span style="color:#4a5a70;font-size:13.5px">
                        Thông tin chỉ được sử dụng để phục vụ việc kiểm tra điều kiện và tư vấn NOXH.
                    </span>
                </div>
            </div>

            <label class="nx-consent__agree">
                <input type="checkbox" id="nx-dong-y">
                <span>
                    Tôi đã đọc và đồng ý với
                    <a href="{{ url('/chinh-sach-bao-mat') }}">chính sách bảo mật thông tin</a>
                </span>
            </label>
        </div>

        <button type="submit" class="nx-btn" id="nx-bat-dau" style="font-size:17px;padding:16px 46px" disabled>
            BẮT ĐẦU KIỂM TRA
            @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 18])
        </button>
    </form>

    <p style="margin-top:12px;color:#8695aa;font-size:13px">
        100% miễn phí · Không lưu thông tin nếu bạn không đồng ý
    </p>

    <div class="nx-alert nx-alert--info" style="margin-top:26px;text-align:left">
        Kết quả chỉ mang tính tham khảo. Việc xác định đủ điều kiện mua NOXH được
        thực hiện dựa trên hồ sơ và quy định áp dụng tại thời điểm xét duyệt.
    </div>
</div>

@push('script')
<script>
    (function () {
        var tich = document.getElementById('nx-dong-y');
        var nut = document.getElementById('nx-bat-dau');
        if (!tich || !nut) return;
        tich.addEventListener('change', function () { nut.disabled = !tich.checked; });
    })();
</script>
@endpush
@endsection
