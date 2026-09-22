@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Tài chính' => url('/tai-chinh'), 'Tính khoản vay' => ''],
    'tieuDe' => 'Tính khoản vay mua nhà ở xã hội',
    'moTa' => 'Nhập giá căn hộ và tỉ lệ vay để ước tính số tiền phải trả hàng tháng.',
])

<div class="nx__container" style="padding-bottom:40px">
    <div class="nx-calc">
        <div class="nx-panel">
            <h2 class="nx-panel__title">Thông tin khoản vay</h2>

            <div class="nx-grid-2">
                <div class="nx-field">
                    <label for="gia">Giá căn hộ (triệu đồng)</label>
                    <input type="number" id="gia" value="1000" min="0" step="10" data-nx-loan>
                </div>
                <div class="nx-field">
                    <label for="tile">Tỉ lệ vay (%)</label>
                    <input type="number" id="tile" value="70" min="0" max="100" step="1" data-nx-loan>
                </div>
                <div class="nx-field">
                    <label for="kyhan">Kỳ hạn vay (năm)</label>
                    <input type="number" id="kyhan" value="20" min="1" max="30" step="1" data-nx-loan>
                </div>
                <div class="nx-field">
                    <label for="goi">Gói vay</label>
                    <select id="goi" data-nx-loan>
                        <option value="">— Nhập lãi suất thủ công —</option>
                        @foreach($goiVay as $g)
                            <option value="{{ $g->id }}"
                                    data-uu-dai="{{ $g->preferential_rate }}"
                                    data-thang-uu-dai="{{ $g->preferential_months }}"
                                    data-sau-uu-dai="{{ $g->standard_rate }}">
                                {{ $g->bank_name }}@if($g->package_name) — {{ $g->package_name }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="nx-field">
                    <label for="ls1">Lãi suất ưu đãi (%/năm)</label>
                    <input type="number" id="ls1" value="4.8" min="0" step="0.1" data-nx-loan>
                </div>
                <div class="nx-field">
                    <label for="thang1">Số tháng ưu đãi</label>
                    <input type="number" id="thang1" value="60" min="0" step="1" data-nx-loan>
                </div>
                <div class="nx-field">
                    <label for="ls2">Lãi suất sau ưu đãi (%/năm)</label>
                    <input type="number" id="ls2" value="9.5" min="0" step="0.1" data-nx-loan>
                </div>
            </div>

            <p style="color:#8695aa;font-size:12.5px;margin:0">
                Kết quả tính theo phương pháp trả góp đều (annuity). Lãi suất ưu đãi chỉ áp
                dụng trong số tháng đã nhập, sau đó áp lãi suất thả nổi.
            </p>
        </div>

        <div class="nx-calc__result">
            <div style="font-size:13px;opacity:.85">Số tiền vay</div>
            <div class="nx-calc__big" id="kq-vay">—</div>

            <div class="nx-calc__row">
                <span>Trả hàng tháng (giai đoạn ưu đãi)</span>
                <strong id="kq-thang1">—</strong>
            </div>
            <div class="nx-calc__row">
                <span>Trả hàng tháng (sau ưu đãi)</span>
                <strong id="kq-thang2">—</strong>
            </div>
            <div class="nx-calc__row">
                <span>Tổng lãi ước tính</span>
                <strong id="kq-lai">—</strong>
            </div>
            <div class="nx-calc__row">
                <span>Tổng phải trả</span>
                <strong id="kq-tong">—</strong>
            </div>

            <p class="nx-calc__note">
                Con số mang tính tham khảo. Số tiền thực tế phụ thuộc vào chính sách của
                từng ngân hàng tại thời điểm giải ngân.
            </p>
        </div>
    </div>

    @if($goiVay->count())
        <div class="nx-panel" style="margin-top:18px">
            <h2 class="nx-panel__title">Các gói vay hiện có</h2>
            <div class="nx-scroll-x">
                <table class="nx-criteria">
                <thead>
                    <tr>
                        <th>Ngân hàng</th>
                        <th>Gói vay</th>
                        <th>Ưu đãi</th>
                        <th>Sau ưu đãi</th>
                        <th>Vay tối đa</th>
                        <th>Kỳ hạn</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($goiVay as $g)
                        <tr>
                            <td><strong>{{ $g->bank_name }}</strong></td>
                            <td>{{ $g->package_name ?: '—' }}</td>
                            <td>{{ $g->preferential_rate !== null ? so_gon($g->preferential_rate) . '%/năm · ' . ($g->preferential_months ?: 0) . ' tháng' : '—' }}</td>
                            <td>{{ $g->standard_rate !== null ? so_gon($g->standard_rate) . '%/năm' : '—' }}</td>
                            <td>{{ $g->max_loan_ratio !== null ? so_gon($g->max_loan_ratio) . '%' : '—' }}</td>
                            <td>{{ $g->max_term_years ? $g->max_term_years . ' năm' : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                </div>
        </div>
    @endif
</div>

@push('script')
<script>
(function () {
    var o = function (id) { return document.getElementById(id); };
    // O nhap dung don vi trieu; khoan vay mua nha thuong len toi hang ty nen
    // de nguyen "1.200 trieu" thi nguoi doc phai tu quy doi.
    var dinhDang = function (trieu) {
        return window.NX.tienTuTrieu(trieu);
    };

    // Cong thuc tra gop deu: mot ky han lai suat r, n ky.
    // Lai suat 0 thi cong thuc chia cho 0 - phai tach rieng ra.
    var traHangThang = function (goc, laiNam, soThang) {
        if (soThang <= 0) return 0;
        var r = laiNam / 100 / 12;
        if (r === 0) return goc / soThang;
        return goc * r / (1 - Math.pow(1 + r, -soThang));
    };

    var tinh = function () {
        var gia = parseFloat(o('gia').value) || 0;
        var tiLe = parseFloat(o('tile').value) || 0;
        var nam = parseFloat(o('kyhan').value) || 0;
        var ls1 = parseFloat(o('ls1').value) || 0;
        var thang1 = parseInt(o('thang1').value, 10) || 0;
        var ls2 = parseFloat(o('ls2').value) || 0;

        var vay = gia * tiLe / 100;
        var tongThang = Math.round(nam * 12);
        thang1 = Math.min(thang1, tongThang);

        var m1 = traHangThang(vay, ls1, tongThang);
        var m2 = traHangThang(vay, ls2, tongThang);

        // Uoc tinh: giai doan uu dai tra theo m1, phan con lai theo m2.
        var tong = m1 * thang1 + m2 * (tongThang - thang1);

        o('kq-vay').textContent = dinhDang(vay);
        o('kq-thang1').textContent = dinhDang(m1);
        o('kq-thang2').textContent = dinhDang(m2);
        o('kq-lai').textContent = dinhDang(Math.max(0, tong - vay));
        o('kq-tong').textContent = dinhDang(tong);
    };

    // Chon goi vay thi dien san ba o lai suat.
    var goi = o('goi');
    if (goi) {
        goi.addEventListener('change', function () {
            var op = goi.options[goi.selectedIndex];
            if (!op || !op.value) return;
            if (op.dataset.uuDai) o('ls1').value = op.dataset.uuDai;
            if (op.dataset.thangUuDai) o('thang1').value = op.dataset.thangUuDai;
            if (op.dataset.sauUuDai) o('ls2').value = op.dataset.sauUuDai;
            tinh();
        });
    }

    document.querySelectorAll('[data-nx-loan]').forEach(function (e) {
        e.addEventListener('input', tinh);
    });

    tinh();
})();
</script>
@endpush
@endsection
