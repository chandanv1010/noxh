
@include('backend.dashboard.component.breadcrumb', ['title' => $config['seo']['index']['title']])
<div class="row mt20">
    <div class="col-lg-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{{ $config['seo']['index']['table']; }} </h5>
                @include('backend.dashboard.component.toolbox', ['model' => 'UserCatalogue'])
            </div>
            <div class="ibox-content">
                @include('backend.user.catalogue.component.filter')
                @include('backend.user.catalogue.component.table')
            </div>
        </div>
    </div>
</div>

<script>
// Bật/tắt cờ "Là nhóm nhân viên kinh doanh" ngay trong danh sách.
//
// Cờ này quyết định thành viên của nhóm đăng nhập ở /sale hay trang quản trị, và
// có xuất hiện ở ô chọn "nhân viên phụ trách" trong form dự án hay không. Đổi
// ngay tại đây cho đỡ phải mở từng nhóm ra sửa.
(function () {
    // Trang nay KHONG co the <form> nao mang @csrf: o loc ben tren la form GET.
    // Doc token tu input[name="_token"] vi vay luon ra null, fetch gui len
    // X-CSRF-TOKEN rong va Laravel tra 419 - cong tac bat len roi tu nhay ve,
    // kem mot hop thoai bao loi kho hieu. Token that nam o the meta trong
    // backend/dashboard/component/head.blade.php, dung chung voi library.js.
    var theMeta = document.querySelector('meta[name="csrf-token"]');
    var oToken = document.querySelector('input[name="_token"]');
    var token = (theMeta && theMeta.getAttribute('content'))
        || (oToken && oToken.value)
        || '';

    var MAU = @json(route('user.catalogue.is-sale', ['id' => '__ID__']));

    document.querySelectorAll('.doi-co-sale').forEach(function (o) {
        // Switchery an the input goc di va tu dat lai thuoc tinh disabled khi
        // khoi tao, nen dat o.disabled luc dang gui KHONG chan duoc cu bam thu
        // hai. Dung mot co rieng.
        var dangGui = false;

        o.addEventListener('change', function () {
            if (dangGui) return;

            var bat = o.checked ? 1 : 0;
            var id = o.getAttribute('data-id');

            dangGui = true;

            fetch(MAU.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ is_sale: bat })
            })
                .then(function (r) {
                    // Het han phien dang nhap thi may chu tra ve trang HTML chu
                    // khong phai JSON - noi thang ra thay vi de r.json() nem
                    // mot loi "Unexpected token <" khong ai hieu.
                    if (r.status === 419) throw new Error('Phiên đăng nhập đã hết hạn, hãy tải lại trang.');
                    return r.json();
                })
                .then(function (kq) {
                    if (!kq.ok) throw new Error(kq.loi || 'Không lưu được');
                })
                .catch(function (e) {
                    // Tra lai trang thai cu: de nguyen thi giao dien noi mot dang
                    // ma CSDL lai mot dang, rat de hieu nham la da luu duoc.
                    o.checked = !o.checked;
                    if (o.nextElementSibling && o.nextElementSibling.classList.contains('switchery')) {
                        // Switchery ve cong tac bang the <span> rieng, doi
                        // o.checked khong keo no ve theo. Ve lai cho khop.
                        o.nextElementSibling.remove();
                        new Switchery(o, { color: '#1AB394', size: 'small' });
                    }
                    alert('Không đổi được cờ nhóm: ' + e.message);
                })
                .then(function () { dangGui = false; });
        });
    });
})();
</script>

