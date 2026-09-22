@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Tài chính' => url('/tai-chinh'), 'Khả năng tài chính' => ''],
    'tieuDe' => 'Tính khả năng tài chính',
    'moTa' => 'Nhập thu nhập và chi tiêu hàng tháng để ước tính mức giá căn hộ phù hợp với bạn.',
])

<div class="nx__container" style="padding-bottom:40px">
    <div class="nx-calc">
        <div class="nx-panel">
            <h2 class="nx-panel__title">Thu nhập &amp; chi tiêu</h2>

            <div class="nx-grid-2">
                <div class="nx-field">
                    <label for="thunhap">Tổng thu nhập hàng tháng (triệu)</label>
                    <input type="number" id="thunhap" value="25" min="0" step="1" data-nx-cap>
                </div>
                <div class="nx-field">
                    <label for="chitieu">Chi tiêu hàng tháng (triệu)</label>
                    <input type="number" id="chitieu" value="12" min="0" step="1" data-nx-cap>
                </div>
                <div class="nx-field">
                    <label for="tietkiem">Tiền đã có (triệu)</label>
                    <input type="number" id="tietkiem" value="300" min="0" step="10" data-nx-cap>
                </div>
                <div class="nx-field">
                    <label for="kyhan2">Kỳ hạn vay (năm)</label>
                    <input type="number" id="kyhan2" value="20" min="1" max="30" step="1" data-nx-cap>
                </div>
                <div class="nx-field">
                    <label for="lscap">Lãi suất bình quân (%/năm)</label>
                    <input type="number" id="lscap" value="6.5" min="0" step="0.1" data-nx-cap>
                </div>
                <div class="nx-field">
                    <label for="tyle-tra">Tỉ lệ thu nhập dành trả nợ (%)</label>
                    <input type="number" id="tyle-tra" value="40" min="10" max="70" step="5" data-nx-cap>
                </div>
            </div>

            <p style="color:#8695aa;font-size:12.5px;margin:0">
                Ngân hàng thường chỉ chấp nhận khoản trả nợ hàng tháng dưới 40–50% thu nhập
                còn lại sau chi tiêu. Vượt ngưỡng này hồ sơ rất khó được duyệt.
            </p>
        </div>

        <div class="nx-calc__result">
            <div style="font-size:13px;opacity:.85">Giá căn hộ phù hợp</div>
            <div class="nx-calc__big" id="cap-gia">—</div>

            <div class="nx-calc__row"><span>Còn lại mỗi tháng</span><strong id="cap-conlai">—</strong></div>
            <div class="nx-calc__row"><span>Khả năng trả nợ / tháng</span><strong id="cap-tra">—</strong></div>
            <div class="nx-calc__row"><span>Số tiền vay được</span><strong id="cap-vay">—</strong></div>
            <div class="nx-calc__row"><span>Vốn tự có</span><strong id="cap-von">—</strong></div>

            <p class="nx-calc__note" id="cap-canhbao">
                Con số mang tính tham khảo, chưa tính các khoản phí khác.
            </p>
        </div>
    </div>
</div>

@push('script')
<script>
(function () {
    var o = function (id) { return document.getElementById(id); };
    var dd = function (t) {
        if (!isFinite(t) || t < 0) return '—';
        return window.NX.tienTuTrieu(t);
    };

    var tinh = function () {
        var thu = parseFloat(o('thunhap').value) || 0;
        var chi = parseFloat(o('chitieu').value) || 0;
        var von = parseFloat(o('tietkiem').value) || 0;
        var nam = parseFloat(o('kyhan2').value) || 0;
        var ls = parseFloat(o('lscap').value) || 0;
        var tyLe = parseFloat(o('tyle-tra').value) || 0;

        var conLai = thu - chi;
        var traDuoc = Math.max(0, conLai * tyLe / 100);
        var soThang = Math.round(nam * 12);
        var r = ls / 100 / 12;

        // Dao nguoc cong thuc tra gop deu de ra so tien vay toi da.
        var vay = 0;
        if (soThang > 0) {
            vay = r === 0 ? traDuoc * soThang : traDuoc * (1 - Math.pow(1 + r, -soThang)) / r;
        }

        o('cap-conlai').textContent = dd(conLai);
        o('cap-tra').textContent = dd(traDuoc);
        o('cap-vay').textContent = dd(vay);
        o('cap-von').textContent = dd(von);
        o('cap-gia').textContent = dd(vay + von);

        o('cap-canhbao').textContent = conLai <= 0
            ? 'Chi tiêu đang bằng hoặc vượt thu nhập — chưa đủ điều kiện vay mua nhà.'
            : 'Con số mang tính tham khảo, chưa tính các khoản phí khác.';
    };

    document.querySelectorAll('[data-nx-cap]').forEach(function (e) {
        e.addEventListener('input', tinh);
    });

    tinh();
})();
</script>
@endpush
@endsection
