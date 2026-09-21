@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.crumb', [
    'crumbs' => ['Kiểm tra điều kiện' => url('/kiem-tra-dieu-kien'), 'Câu hỏi' => ''],
])

<div class="nx__container">
    <h1 class="nx__heading" style="font-size:27px">Kiểm tra điều kiện mua nhà ở xã hội</h1>
    <p class="nx__subheading">
        Trả lời {{ $cauHoi->count() }} câu hỏi ngắn để biết bạn có đủ điều kiện mua NOXH
        theo quy định hiện hành hay không.
    </p>

    <div class="nx-progress">
        <div class="nx-progress__step is-active"><span>1</span><br>Trả lời câu hỏi</div>
        <div class="nx-progress__step"><span>2</span><br>Xem kết quả</div>
        <div class="nx-progress__step"><span>3</span><br>Gợi ý hồ sơ</div>
        <div class="nx-progress__step"><span>4</span><br>Nhận tư vấn</div>
    </div>
</div>

<div class="nx-check">
    <form method="POST" action="{{ route('noxh.check.submit') }}">
        @csrf

        @include('frontend.noxh.component.alert')

        @foreach($nhom as $maNhom => $tenNhom)
            @php $ds = $cauHoiTheoNhom[$maNhom] ?? collect(); @endphp
            @continue(!$ds->count())

            <div class="nx-panel">
                <h2 class="nx-panel__title">{{ $tenNhom }}</h2>

                @foreach($ds as $ch)
                    @php $soThuTu = $loop->parent->index > 0 ? null : null; @endphp
                    <div class="nx-question{{ $ch->options->count() > 2 ? ' nx-question--wide' : '' }}">
                        <span class="nx-question__no">{{ $ch->order ?: $loop->iteration }}</span>

                        <div class="nx-question__text">
                            {{ $ch->question }}
                            @if($ch->hint)
                                <small>{{ $ch->hint }}</small>
                            @endif
                        </div>

                        <div class="nx-question__answers">
                            @forelse($ch->options as $da)
                                <label class="nx-question__option">
                                    <input type="radio" name="traLoi[{{ $ch->id }}]" value="{{ $da->value }}"
                                           {{ old('traLoi.' . $ch->id) === $da->value ? 'checked' : '' }}>
                                    <span>{{ $da->label }}</span>
                                </label>
                            @empty
                                {{-- Cau hoi chua nhap dap an nao thi cho go tu do, van ghi
                                     nhan duoc cau tra loi thay vi bo trong o. --}}
                                <input type="text" name="traLoi[{{ $ch->id }}]" class="nx-field"
                                       value="{{ old('traLoi.' . $ch->id) }}"
                                       placeholder="Nhập câu trả lời"
                                       style="grid-column:1/-1;padding:10px 12px;border:1px solid #e3ebf6;border-radius:8px;width:100%">
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="nx-panel">
            <h2 class="nx-panel__title">Nhận kết quả</h2>
            <p class="nx__subheading">Để lại thông tin để xem kết quả và được chuyên viên hỗ trợ.</p>

            <div class="nx-grid-2">
                <div class="nx-field">
                    <label for="ten">Họ và tên <span style="color:#dc2626">*</span></label>
                    <input type="text" id="ten" name="name" value="{{ old('name') }}" required>
                </div>
                <div class="nx-field">
                    <label for="sdt">Số điện thoại <span style="color:#dc2626">*</span></label>
                    <input type="tel" id="sdt" name="phone" value="{{ old('phone') }}" required>
                </div>
            </div>

            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a href="{{ url('/kiem-tra-dieu-kien') }}" class="nx-btn nx-btn--line">Bắt đầu lại</a>
                <button type="submit" class="nx-btn">
                    XEM KẾT QUẢ
                    @include('frontend.noxh.component.icon', ['name' => 'arrow-right', 'size' => 16])
                </button>
            </div>

            <p style="margin:14px 0 0;color:#8695aa;font-size:12.5px">
                Thông tin bạn cung cấp được bảo mật tuyệt đối và chỉ sử dụng để kiểm tra điều kiện mua NOXH.
            </p>
        </div>
    </form>

    <aside>
        @include('frontend.noxh.component.expert-box')

        <div class="nx-panel">
            <h2 class="nx-panel__title">Tra cứu kết quả cũ</h2>
            <p class="nx__subheading">Nhập mã kết quả đã nhận để xem lại. Mã có hiệu lực 30 ngày.</p>
            <form method="POST" action="{{ route('noxh.check.lookup') }}">
                @csrf
                <div class="nx-field">
                    <input type="text" name="code" placeholder="Ví dụ: NOXH-260921-1530">
                </div>
                <button type="submit" class="nx-btn nx-btn--ghost nx-btn--block">TRA CỨU</button>
            </form>
        </div>

        <div class="nx-panel">
            <ul class="nx-ticks">
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) 100% miễn phí</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Bảo mật thông tin</li>
                <li>@include('frontend.noxh.component.icon', ['name' => 'check-circle', 'size' => 16]) Cập nhật theo luật mới</li>
            </ul>
        </div>
    </aside>
</div>
@endsection
