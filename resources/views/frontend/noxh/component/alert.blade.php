{{-- Thong bao sau khi gui form. Loi cua tung o van hien ngay duoi o do. --}}
@if(session('nx_success'))
    <div class="nx-alert nx-alert--success">{{ session('nx_success') }}</div>
@endif

@if(session('nx_error'))
    <div class="nx-alert nx-alert--error">{{ session('nx_error') }}</div>
@endif

@if($errors->any())
    <div class="nx-alert nx-alert--error">
        <ul>
            @foreach($errors->all() as $loi)
                <li>{{ $loi }}</li>
            @endforeach
        </ul>
    </div>
@endif
