<div class="row border-bottom">
    <nav class="navbar navbar-static-top white-bg" role="navigation" style="margin-bottom:0">
        <div class="navbar-header">
            <a class="navbar-minimalize minimalize-styl-2 btn btn-primary" href="#"><i class="fa fa-bars"></i></a>
            <span style="display:inline-block;padding:20px 0 0 12px;font-weight:600">{{ $tieuDe ?? '' }}</span>
        </div>
        <ul class="nav navbar-top-links navbar-right">
            <li>
                <a href="{{ url('/') }}" target="_blank">
                    <i class="fa fa-external-link"></i> Xem website
                </a>
            </li>
            <li>
                <a href="{{ route('sale.logout') }}">
                    <i class="fa fa-sign-out"></i> Đăng xuất
                </a>
            </li>
        </ul>
    </nav>
</div>
