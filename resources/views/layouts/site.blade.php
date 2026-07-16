<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', $settings['brand_name'] ?? 'Minh Triệu Party')</title>
    <meta name="description" content="@yield('description', $settings['about'] ?? '')"><meta name="robots" content="index,follow,max-image-preview:large"><link rel="canonical" href="@yield('canonical', url()->current())">
    <meta property="og:locale" content="vi_VN"><meta property="og:type" content="@yield('og_type', 'website')"><meta property="og:site_name" content="{{ $settings['brand_name'] }}"><meta property="og:title" content="@yield('title', $settings['brand_name'])"><meta property="og:description" content="@yield('description', $settings['about'])"><meta property="og:url" content="@yield('canonical', url()->current())">
    @hasSection('og_image')<meta property="og:image" content="@yield('og_image')">@endif
    <meta name="twitter:card" content="summary_large_image">
    <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'LocalBusiness','name'=>$settings['brand_name'],'description'=>$settings['about'],'telephone'=>$settings['phone'],'address'=>$settings['address'],'url'=>route('home'),'sameAs'=>array_values(array_filter([$settings['facebook'],$settings['fanpage']]))], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/party.css') }}" rel="stylesheet"><link href="{{ asset('css/home.css') }}" rel="stylesheet"><link href="{{ asset('css/navigation.css') }}" rel="stylesheet"><link href="{{ asset('css/category-page.css') }}" rel="stylesheet">
</head>
<body>
<header class="site-header"><div class="site-nav"><a class="brand" href="{{ route('home') }}"><span class="brand-mark">🎈</span><span>{{ $settings['brand_name'] }}<small>Party & Entertainment</small></span></a><button class="menu-button" aria-label="Mở menu" onclick="document.querySelector('.main-nav').classList.toggle('open')">☰</button><nav class="main-nav"><a href="{{ route('home') }}">Trang chủ</a><a href="{{ route('services') }}">Dịch vụ</a><a href="{{ route('events') }}">Bài viết & hình ảnh</a><a class="nav-contact" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a></nav></div></header>
<main>@yield('content')</main>
<div class="floating-contact"><a class="float-phone" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}" aria-label="Gọi điện">☎</a>@if($settings['facebook'])<a class="float-facebook" href="{{ $settings['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook">f</a>@endif</div>
<footer class="party-footer"><div class="footer-grid"><div><a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark">🎈</span><span>{{ $settings['brand_name'] }}</span></a><p>{{ $settings['about'] }}</p></div><div><h3>Dịch vụ</h3><a href="{{ route('services') }}">Trang trí & bong bóng</a><a href="{{ route('services') }}">Biểu diễn & trò chơi</a><a href="{{ route('services') }}">Ẩm thực sự kiện</a><a href="{{ route('services') }}">Âm thanh & âm nhạc</a></div><div><h3>Liên hệ</h3><a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>@if($settings['facebook'])<a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Facebook cá nhân</a>@endif @if($settings['fanpage'])<a href="{{ $settings['fanpage'] }}" target="_blank" rel="noopener">Fanpage</a>@endif<p>📍 {{ $settings['address'] }}</p></div></div><div class="footer-bottom">© {{ date('Y') }} {{ $settings['brand_name'] }} · Website giới thiệu dịch vụ và bài viết sự kiện.</div></footer>
</body>
</html>
