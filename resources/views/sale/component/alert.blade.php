{{-- Trang quan tri hien khong in thong bao nao ra man hinh du cac service van
     gui session('success'). O day in han ra de nguoi dung biet viec da xong. --}}
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <ul style="margin:0;padding-left:18px">
            @foreach($errors->all() as $loi)
                <li>{{ $loi }}</li>
            @endforeach
        </ul>
    </div>
@endif
