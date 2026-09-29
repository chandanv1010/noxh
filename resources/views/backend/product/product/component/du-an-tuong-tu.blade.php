{{--
    Chon du an hien o khoi "DU AN TUONG TU TAI ..." cua trang chi tiet.

    Truoc day khoi do tu doc ra ba du an cung tinh. Nhung du an gan nhau ve
    dia ly chua chac thay the duoc cho nhau, nen viec chon de cho nguoi biet
    thi truong quyet dinh.

    Giong khoi nhan vien: phai co o an lam dau, vi trinh duyet khong gui o
    chon nhieu khi khong chon gi - thieu no thi "bo het" se thanh "khong luu".
--}}
@php
    $dangChon = [];
    if (isset($product) && $product) {
        foreach ($product->duAnTuongTu as $d) {
            $dangChon[] = $d->id;
        }
    }
    $dangChon = old('du_an_tuong_tu', $dangChon);
    $tuChon = collect($duAnKhac ?? [])->filter(fn ($d) => !isset($product) || !$product || $d->id !== $product->id);
@endphp

<div class="ibox w">
    <div class="ibox-title">
        <h5>Dự án tương tự</h5>
    </div>
    <div class="ibox-content">
        <input type="hidden" name="co_gan_tuong_tu" value="1">

        @if($tuChon->count())
            <div class="form-row">
                <select multiple name="du_an_tuong_tu[]" class="form-control setupSelect2">
                    @foreach($tuChon as $d)
                        <option value="{{ $d->id }}" {{ in_array($d->id, $dangChon) ? 'selected' : '' }}>
                            {{ $d->name }}@if($d->code) — {{ $d->code }}@endif
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    Thứ tự chọn chính là thứ tự hiện ra trang. Để trống thì trang chi tiết
                    tự lấy dự án cùng tỉnh.
                </small>
            </div>
        @else
            <p class="text-muted" style="margin:0;font-size:13px">Chưa có dự án nào khác để chọn.</p>
        @endif
    </div>
</div>
