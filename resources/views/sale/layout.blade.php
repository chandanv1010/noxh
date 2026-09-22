{{--
    Khung cua bang dieu khien nhan vien kinh doanh.

    Dung lai head va script cua trang quan tri: hai file do nap CKEditor,
    select2, trinh chon anh va thu vien location.js ma form du an can. Viet
    lai mot bo rieng chi de doi mau thanh ben trai la nhan doi cong bao tri.

    Rieng thanh ben va thanh tren thi lam moi - menu cua quan tri co 13 module
    ma nhan vien kinh doanh khong duoc dung mot muc nao trong so do.
--}}
<!DOCTYPE html>
<html lang="vi">

<head>
    @include('backend.dashboard.component.head')
    <title>{{ $tieuDe ?? 'Bảng điều khiển' }} - Nhân viên kinh doanh</title>
</head>

<body>
    <div id="wrapper">
        @include('sale.component.sidebar')

        <div id="page-wrapper" class="gray-bg">
            @include('sale.component.nav')

            <div class="wrapper wrapper-content animated fadeInRight">
                @include('sale.component.alert')
                @include($template)
            </div>
        </div>
    </div>

    @include('backend.dashboard.component.script')
</body>

</html>
