{{--
    Bo icon dung chung, ve thang bang SVG.

    Khong dung font icon: chi can khoang 20 hinh, keo ca mot bo font ve chi de
    dung tung do la lang phi va lam cham trang.

    Cach dung:  @include('frontend.noxh.component.icon', ['name' => 'pin'])
                @include('frontend.noxh.component.icon', ['name' => 'pin', 'size' => 20])
--}}
@php
    $size = $size ?? 16;
    $duong = [
        'pin' => '<path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.6"/>',
        'phone' => '<path d="M21 16.9v2.2a2 2 0 0 1-2.2 2 19.4 19.4 0 0 1-8.5-3 19 19 0 0 1-5.9-5.9 19.4 19.4 0 0 1-3-8.6A2 2 0 0 1 3.4 1.4h2.2a2 2 0 0 1 2 1.7c.1 1 .3 1.9.7 2.8a2 2 0 0 1-.5 2.1L6.9 9.1a15.6 15.6 0 0 0 5.9 5.9l1.1-1.1a2 2 0 0 1 2.1-.5c.9.4 1.8.6 2.8.7a2 2 0 0 1 1.7 2z" transform="translate(0 1.5)"/>',
        'mail' => '<rect x="2.5" y="4.5" width="19" height="15" rx="2.2"/><path d="m3 6 9 6.5L21 6"/>',
        'globe' => '<circle cx="12" cy="12" r="9.2"/><path d="M3 12h18M12 2.8c2.4 2.5 3.7 5.8 3.7 9.2S14.4 18.7 12 21.2c-2.4-2.5-3.7-5.8-3.7-9.2S9.6 5.3 12 2.8z"/>',
        'clock' => '<circle cx="12" cy="12" r="9.2"/><path d="M12 6.8V12l3.4 2"/>',
        'check' => '<path d="m4.5 12.5 5 5 10-11"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9.2"/><path d="m8 12.3 2.8 2.8L16.3 9.5"/>',
        'shield' => '<path d="M12 2.8 4.5 6v6c0 4.6 3.2 8.1 7.5 9.2 4.3-1.1 7.5-4.6 7.5-9.2V6z"/>',
        'shield-check' => '<path d="M12 2.8 4.5 6v6c0 4.6 3.2 8.1 7.5 9.2 4.3-1.1 7.5-4.6 7.5-9.2V6z"/><path d="m9 12 2.2 2.2L15.3 10"/>',
        'building' => '<path d="M4 21V5.5A1.5 1.5 0 0 1 5.5 4h6A1.5 1.5 0 0 1 13 5.5V21"/><path d="M13 10h5.5A1.5 1.5 0 0 1 20 11.5V21"/><path d="M2.5 21h19M7 8h2M7 12h2M7 16h2M16 14h1.5M16 17.5h1.5"/>',
        'home' => '<path d="m3.5 10.5 8.5-7 8.5 7"/><path d="M5.5 9.5V20h13V9.5"/><path d="M10 20v-5.5h4V20"/>',
        'users' => '<circle cx="9" cy="8.5" r="3.4"/><path d="M2.8 20c0-3.4 2.8-5.6 6.2-5.6s6.2 2.2 6.2 5.6"/><path d="M16.5 5.5a3.2 3.2 0 0 1 0 6.2M17.6 14.6c2.2.5 3.6 2.2 3.6 4.6"/>',
        'user' => '<circle cx="12" cy="8.2" r="3.7"/><path d="M4.8 20.2c0-3.8 3.2-6.2 7.2-6.2s7.2 2.4 7.2 6.2"/>',
        'money' => '<rect x="2.5" y="6" width="19" height="12" rx="2.2"/><circle cx="12" cy="12" r="2.8"/><path d="M6 10v4M18 10v4"/>',
        'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        'scale' => '<path d="M12 3.5v17M6 6.5h12M7.5 6.5 4 14h7zM16.5 6.5 13 14h7zM8 20h8"/>',
        'folder' => '<path d="M3 7.2A1.7 1.7 0 0 1 4.7 5.5h4l2 2.4h8.6A1.7 1.7 0 0 1 21 9.6v8.7a1.7 1.7 0 0 1-1.7 1.7H4.7A1.7 1.7 0 0 1 3 18.3z"/>',
        'file' => '<path d="M14 3.2H7.5A1.7 1.7 0 0 0 5.8 5v14a1.7 1.7 0 0 0 1.7 1.7h9A1.7 1.7 0 0 0 18.2 19V7.5z"/><path d="M14 3.2V7.5h4.2"/>',
        'file-text' => '<path d="M14 3.2H7.5A1.7 1.7 0 0 0 5.8 5v14a1.7 1.7 0 0 0 1.7 1.7h9A1.7 1.7 0 0 0 18.2 19V7.5z"/><path d="M14 3.2V7.5h4.2M9 12h6M9 15.5h4"/>',
        'download' => '<path d="M12 3.5v11M7.5 10.5 12 15l4.5-4.5M4.5 19.5h15"/>',
        'calculator' => '<rect x="4.5" y="2.8" width="15" height="18.4" rx="2"/><path d="M8 7h8M8 11.5h1.5M11.5 11.5H13M15 11.5h1.5M8 15.5h1.5M11.5 15.5H13M15 15.5h1.5"/>',
        'bank' => '<path d="M3 9.5 12 4l9 5.5M4.5 9.5V19M9 9.5V19M15 9.5V19M19.5 9.5V19M2.5 19.5h19"/>',
        'question' => '<circle cx="12" cy="12" r="9.2"/><path d="M9.6 9.4a2.5 2.5 0 1 1 3.4 2.3c-.7.3-1 .9-1 1.6v.4"/><circle cx="12" cy="17" r=".9" fill="currentColor" stroke="none"/>',
        'search' => '<circle cx="11" cy="11" r="6.8"/><path d="m16.2 16.2 4.3 4.3"/>',
        'arrow-right' => '<path d="M4.5 12h14M13 6.5l5.5 5.5L13 17.5"/>',
        'arrow-left' => '<path d="M19.5 12h-14M11 6.5 5.5 12 11 17.5"/>',
        'ruler' => '<path d="m3.2 15.6 8.4-8.4 5.2 5.2-8.4 8.4z"/><path d="m14.5 4.3 5.2 5.2M6 12.8l1.6 1.6M9 9.8l1.6 1.6M12 6.8l1.6 1.6"/>',
        'layers' => '<path d="m12 3.2 8.5 4.4-8.5 4.4-8.5-4.4z"/><path d="m3.5 12 8.5 4.4 8.5-4.4M3.5 16.3l8.5 4.4 8.5-4.4"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="2"/><path d="M3.5 9.8h17M8 3v4M16 3v4"/>',
        'eye' => '<path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12z"/><circle cx="12" cy="12" r="2.9"/>',
        'lock' => '<rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7"/>',
        'chat' => '<path d="M20.5 11.6c0 4-3.8 7.2-8.5 7.2a9.8 9.8 0 0 1-2.6-.35L4.5 20.2l1.3-3.4a6.9 6.9 0 0 1-2.3-5.2c0-4 3.8-7.2 8.5-7.2s8.5 3.2 8.5 7.2z"/>',
        'info' => '<circle cx="12" cy="12" r="9.2"/><path d="M12 11v5.5"/><circle cx="12" cy="7.8" r=".9" fill="currentColor" stroke="none"/>',
        'warning' => '<path d="M12 4.2 2.8 20h18.4z"/><path d="M12 10v4.2"/><circle cx="12" cy="17.2" r=".9" fill="currentColor" stroke="none"/>',
        'clipboard' => '<path d="M9 4.5H7.5A1.7 1.7 0 0 0 5.8 6.2V19A1.7 1.7 0 0 0 7.5 20.7h9A1.7 1.7 0 0 0 18.2 19V6.2A1.7 1.7 0 0 0 16.5 4.5H15"/><rect x="9" y="2.8" width="6" height="3.4" rx="1"/><path d="M8.8 12.2 10.4 14l4-4"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
    ];
@endphp
@if(isset($duong[$name]))
    <svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
         aria-hidden="true" focusable="false">{!! $duong[$name] !!}</svg>
@endif
