<base href="{{ config('app.url') }}" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1,user-scalable=0">
<!-- <meta name="viewport" content="width=device-width, initial-scale=1.0"> -->
<!-- Chặn iOS Safari / Android tự động bọc số điện thoại thành link tel: và áp màu
     mặc định của trình duyệt, khiến chữ bị lẫn vào nền tối. -->
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="date=no">
<meta name="format-detection" content="address=no">
<meta name="robots" content="index,follow"/>
<meta name="author" content="{{ $system['homepage_company'] }}"/>
<meta name="copyright" content="{{ $system['homepage_company'] }}" />
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="refresh" content="1800" />
<link rel="icon" href="{{ $system['homepage_favicon'] }}" type="image/png" sizes="30x30">
<!-- GOOGLE -->
<title>{{ $seo['meta_title'] }}</title>
<meta name="description"  content="{{ $seo['meta_description'] }}" />
<meta name="keyword"  content="{{ $seo['meta_keyword'] }}" />
<link rel="canonical" href="{{ $seo['canonical'] }}" />		
<meta property="og:locale" content="vi_VN" />
<!-- for Facebook -->
<meta property="og:title" content="{{ $seo['meta_title'] }}" />
<meta property="og:type" content="website" />
<meta property="og:image" content="{{ $seo['meta_image'] }}" />
<meta property="og:url" content="{{ $seo['canonical'] }}" />		
<meta property="og:description" content="{{ $seo['meta_description'] }}" />
<meta property="og:site_name" content="" />
<meta property="fb:admins" content=""/>
<meta property="fb:app_id" content="" />
<meta name="twitter:card" content="summary" />
<meta name="twitter:title" content="{{ $seo['meta_title'] }}" />
<meta name="twitter:description" content="{{ $seo['meta_description'] }}" />
<meta name="twitter:image" content="{{ $seo['meta_image'] }}" />
<link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="{{ asset('vendor/frontend/library/js/jquery.js') }}"></script>
