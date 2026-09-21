<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $seo['meta_title'] ?? 'NOXH.vn' }}</title>
    <meta name="description" content="{{ $seo['meta_description'] ?? '' }}">
    @if(!empty($seo['meta_keyword']))
        <meta name="keywords" content="{{ $seo['meta_keyword'] }}">
    @endif
    @if(!empty($seo['canonical']))
        <link rel="canonical" href="{{ $seo['canonical'] }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $seo['meta_title'] ?? 'NOXH.vn' }}">
    <meta property="og:description" content="{{ $seo['meta_description'] ?? '' }}">
    @if(!empty($seo['meta_image']))
        <meta property="og:image" content="{{ $seo['meta_image'] }}">
    @endif

    @if(!empty($system['homepage_favicon']))
        <link rel="icon" href="{{ $system['homepage_favicon'] }}" type="image/png">
    @endif

    @vite('resources/css/app.scss')
</head>
<body>

<div class="nx">
    @include('frontend.noxh.component.header')

    @yield('content')

    @include('frontend.noxh.component.footer')
</div>

@include('frontend.noxh.component.script')
@vite('resources/js/app.js')
</body>
</html>
