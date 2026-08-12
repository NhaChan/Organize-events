<!doctype html>
<html lang="vi">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-NZCKT2JLMT"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-NZCKT2JLMT');
    </script>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    @php
        $canonicalUrl = trim($__env->yieldContent("canonical", \App\Support\SeoUrl::asset(request()->getPathInfo())));
        $robotsDirective = ($settings["seo_indexing"] ?? true)
            ? trim($__env->yieldContent("robots", "index,follow,max-image-preview:large"))
            : "noindex,nofollow,noarchive";
    @endphp
    <title>@yield("title", $settings["brand_name"] ?? "Minh Triều Party")</title>
    <meta name="description" content="@yield("description", $settings["about"] ?? "")">
    <meta name="robots" content="{{ $robotsDirective }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:locale" content="vi_VN"><meta property="og:type" content="@yield("og_type", "website")"><meta property="og:site_name" content="{{ $settings["brand_name"] }}"><meta property="og:title" content="@yield("title", $settings["brand_name"])"><meta property="og:description" content="@yield("description", $settings["about"])"><meta property="og:url" content="{{ $canonicalUrl }}">
    @hasSection("og_image")<meta property="og:image" content="@yield("og_image")">@endif
    <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="@yield("title", $settings["brand_name"])"><meta name="twitter:description" content="@yield("description", $settings["about"])">
    <link rel="icon" type="image/svg+xml" href="{{ \App\Support\SeoUrl::asset("favicon.svg") }}?v=4">
    <link rel="alternate icon" type="image/png" sizes="256x256" href="{{ \App\Support\SeoUrl::asset("favicon-balloon.png") }}?v=4">
    <link rel="apple-touch-icon" href="{{ \App\Support\SeoUrl::asset("favicon-balloon.png") }}?v=4">
    <script type="application/ld+json">{!! json_encode(["@context"=>"https://schema.org","@type"=>"LocalBusiness","@id"=>\App\Support\SeoUrl::route("home")."#business","name"=>$settings["brand_name"],"description"=>$settings["about"],"telephone"=>$settings["phone"],"address"=>["@type"=>"PostalAddress","streetAddress"=>$settings["address"],"addressCountry"=>"VN"],"url"=>\App\Support\SeoUrl::route("home"),"logo"=>\App\Support\SeoUrl::asset("images/nina-nina-icon.png"),"sameAs"=>array_values(array_filter([$settings["facebook"],$settings["fanpage"]]))], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) !!}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com"><link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/party.css') }}" rel="stylesheet"><link href="{{ asset('css/home.css') }}" rel="stylesheet"><link href="{{ asset('css/navigation.css') }}" rel="stylesheet"><link href="{{ asset('css/category-page.css') }}?v={{ filemtime(public_path('css/category-page.css')) }}" rel="stylesheet"><link href="{{ asset('css/typography.css') }}?v={{ filemtime(public_path('css/typography.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/mega-menu.css') }}?v=2" rel="stylesheet">
    <link href="{{ asset('css/product-price.css') }}?v={{ filemtime(public_path('css/product-price.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
<x-site-header :settings="$settings" :categories="$navigationCategories" />
<main>@yield('content')</main>
<div class="floating-contact"><a class="float-phone" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}" aria-label="Gọi điện">☎</a>@if($settings['facebook'])<a class="float-facebook" href="{{ $settings['facebook'] }}" target="_blank" rel="noopener" aria-label="Facebook">f</a>@endif</div>
<footer class="party-footer"><div class="footer-grid"><div><a class="brand footer-brand" href="{{ route('home') }}"><span class="brand-mark">🎈</span><span>{{ $settings['brand_name'] }}</span></a><p>{{ $settings['about'] }}</p></div><div><h3>Dịch vụ</h3><a href="{{ route('services') }}">Trang trí & bong bóng</a><a href="{{ route('services') }}">Biểu diễn & trò chơi</a><a href="{{ route('services') }}">Ẩm thực sự kiện</a><a href="{{ route('services') }}">Âm thanh & âm nhạc</a></div><div><h3>Liên hệ</h3><a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>@if($settings['facebook'])<a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Facebook cá nhân</a>@endif @if($settings['fanpage'])<a href="{{ $settings['fanpage'] }}" target="_blank" rel="noopener">Fanpage</a>@endif<p>📍 {{ $settings['address'] }}</p></div></div><div class="footer-bottom">© {{ date('Y') }} {{ $settings['brand_name'] }} · Website giới thiệu dịch vụ và bài viết sự kiện.</div></footer>
</body>
</html>
