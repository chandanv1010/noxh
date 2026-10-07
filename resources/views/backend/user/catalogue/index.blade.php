
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
    var oToken = document.querySelector('input[name="_token"]');
    var MAU = @json(route('user.catalogue.is-sale', ['id' => '__ID__']));

    document.querySelectorAll('.doi-co-sale').forEach(function (o) {
        o.addEventListener('change', function () {
            var bat = o.checked ? 1 : 0;
            var id = o.getAttribute('data-id');

            o.disabled = true;

            fetch(MAU.replace('__ID__', id), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': oToken ? oToken.value : '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ is_sale: bat })
            })
                .then(function (r) { return r.json(); })
                .then(function (kq) {
                    if (!kq.ok) throw new Error(kq.loi || 'Không lưu được');
                })
                .catch(function (e) {
                    // Tra lai trang thai cu: de nguyen thi giao dien noi mot dang
                    // ma CSDL lai mot dang, rat de hieu nham la da luu duoc.
                    o.checked = !o.checked;
                    alert('Không đổi được cờ nhóm: ' + e.message);
                })
                .then(function () { o.disabled = false; });
        });
    });
})();
</script>

