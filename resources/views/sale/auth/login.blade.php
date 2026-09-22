<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Nhân viên kinh doanh</title>
    @vite('resources/css/app_backend.scss')
</head>

<body class="gray-bg">
    <div class="middle-box text-center loginscreen animated fadeInDown" style="margin-top:80px">
        <div>
            <h2 class="font-bold">NOXH.vn</h2>
            <p>Bảng điều khiển dành cho nhân viên kinh doanh.</p>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:18px;text-align:left">
                        @foreach($errors->all() as $loi)
                            <li>{{ $loi }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="m-t" method="post" action="{{ route('sale.login') }}">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" value="{{ old('email') }}" class="form-control"
                           placeholder="Email" required autofocus>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                </div>
                <button type="submit" class="btn btn-primary block full-width m-b">Đăng nhập</button>
            </form>

            <p class="m-t"><small>Quản trị viên đăng nhập tại <a href="{{ route('auth.admin') }}">trang quản trị</a>.</small></p>
        </div>
    </div>
</body>

</html>
