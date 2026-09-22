
@php
   $segment = request()->segment(1);
@endphp
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            {{-- Khoi nay truoc day in cung "David Williams / Art Director" va ba
                 lien ket chet cua ban mau Inspinia (profile.html, contacts.html,
                 mailbox.html). Gio doc dung nguoi dang dang nhap. --}}
            @php
                $toi = auth()->user();
                $chucDanh = $toi?->title ?: ($toi?->user_catalogues->name ?? 'Quản trị viên');
            @endphp
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <span>
                        <img alt="{{ $toi?->name }}" class="img-circle"
                             src="{{ $toi?->image ?: asset('uploads/noxh/avatar-mac-dinh.png') }}"
                             style="width:56px;height:56px;object-fit:cover">
                    </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="clear">
                            <span class="block m-t-xs">
                                <strong class="font-bold">{{ $toi?->name }}</strong>
                            </span>
                            <span class="text-muted text-xs block">{{ $chucDanh }} <b class="caret"></b></span>
                        </span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="{{ route('user.edit', $toi?->id) }}">Hồ sơ của tôi</a></li>
                        <li><a href="{{ url('/') }}" target="_blank">Xem website</a></li>
                        <li class="divider"></li>
                        <li><a href="{{ route('auth.logout') }}">Đăng xuất</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    NOXH
                </div>
            </li>
            @foreach(__('sidebar.module') as $key => $val)
            <li class=" {{ (isset($val['class'])) ? $val['class'] : '' }} {{ (in_array($segment, $val['name'])) ? 'active' : '' }}">
                <a href="{{ (isset($val['route'])) ? $val['route'] : '' }}">
                    <i class="{{ $val['icon'] }}"></i> 
                    <span class="nav-label">{{ $val['title'] }}</span> 
                    @if(isset($val['subModule']) && count($val['subModule']))
                    <span class="fa arrow"></span>
                    @endif
                </a>
                @if(isset($val['subModule']))
                <ul class="nav nav-second-level">
                    @foreach($val['subModule'] as $module)
                    <li><a href="{{ $module['route'] }}">{{ $module['title'] }}</a></li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
</nav>