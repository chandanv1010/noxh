@extends('frontend.noxh.layout')

@section('content')
@include('frontend.noxh.component.page-head', [
    'crumbs' => ['Liên hệ' => ''],
    'tieuDe' => 'Liên hệ với NOXH.vn',
    'moTa' => 'Để lại thông tin hoặc gọi trực tiếp, chuyên viên sẽ hỗ trợ bạn trong giờ hành chính.',
])

<div class="nx-listing nx-listing--right">
    <div class="nx-panel">
        <h2 class="nx-panel__title">Thông tin liên hệ</h2>

        <table class="nx-table">
            <tbody>
                @if(!empty($system['contact_hotline']))
                    <tr>
                        <th>Hotline</th>
                        <td>
                            @foreach(array_filter(array_map('trim', explode('|', $system['contact_hotline']))) as $i => $so)
                                @if($i > 0) · @endif
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $so) }}">{{ $so }}</a>
                            @endforeach
                        </td>
                    </tr>
                @endif
                @if(!empty($system['contact_email']))
                    <tr><th>Email</th><td><a href="mailto:{{ $system['contact_email'] }}">{{ $system['contact_email'] }}</a></td></tr>
                @endif
                @if(!empty($system['contact_address']))
                    <tr><th>Địa chỉ</th><td>{{ $system['contact_address'] }}</td></tr>
                @endif
                @if(!empty($system['contact_working_hours']))
                    <tr><th>Giờ làm việc</th><td>{{ $system['contact_working_hours'] }}</td></tr>
                @endif
            </tbody>
        </table>

        @if(!empty($system['contact_map']))
            <div style="margin-top:16px">{!! $system['contact_map'] !!}</div>
        @endif
    </div>

    <aside>
        @include('frontend.noxh.component.lead-form', [
            'tieuDe' => 'Gửi thông tin liên hệ',
            'moTa' => 'Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất.',
            'nguon' => 'contact',
        ])
    </aside>
</div>
@endsection
