@php
    $duong = request()->segment(2);
@endphp
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header" style="padding:24px 25px">
                <div class="profile-element">
                    @if(auth()->user()->image)
                        <img alt="{{ auth()->user()->name }}" class="img-circle"
                             src="{{ auth()->user()->image }}" style="width:56px;height:56px;object-fit:cover">
                    @endif
                    <div class="m-t-xs">
                        <strong class="font-bold" style="color:#fff">{{ auth()->user()->name }}</strong>
                        <div class="text-muted text-xs">{{ auth()->user()->title ?: 'Nhân viên kinh doanh' }}</div>
                    </div>
                </div>
                <div class="logo-element">NVKD</div>
            </li>

            <li class="{{ is_null($duong) ? 'active' : '' }}">
                <a href="{{ route('sale.dashboard') }}">
                    <i class="fa fa-dashboard"></i> <span class="nav-label">Tổng quan</span>
                </a>
            </li>
            <li class="{{ $duong === 'du-an' ? 'active' : '' }}">
                <a href="{{ route('sale.project.index') }}">
                    <i class="fa fa-building"></i> <span class="nav-label">Dự án của tôi</span>
                </a>
            </li>
            <li class="{{ $duong === 'bai-viet' ? 'active' : '' }}">
                <a href="{{ route('sale.post.index') }}">
                    <i class="fa fa-newspaper-o"></i> <span class="nav-label">Bài viết của tôi</span>
                </a>
            </li>
            <li class="{{ $duong === 'ho-so' ? 'active' : '' }}">
                <a href="{{ route('sale.profile') }}">
                    <i class="fa fa-user"></i> <span class="nav-label">Hồ sơ của tôi</span>
                </a>
            </li>
        </ul>
    </div>
</nav>
