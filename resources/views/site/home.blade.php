@extends('layouts.site')

@section('title', $settings['brand_name'].' - Trang trí tiệc và dịch vụ sự kiện')
@section('description', 'Dịch vụ trang trí tiệc, biểu diễn, ẩm thực và giải trí sự kiện của '.$settings['brand_name'].'. Xem hình ảnh thực tế và liên hệ tư vấn nhanh.')
@section("canonical", \App\Support\SeoUrl::route("home"))

@section('content')
@php
    $icons = ['🎈', '🎩', '🤡', '🍭', '🍿', '🌳', '🦫', '🫧', '🎵', '🎤'];
    $heroEvent = $latest->first(fn ($event) => filled($event->thumbnail));
    $heroCategory = $categories->first(fn ($category) => filled($category->page?->service_image) || filled($category->page?->banner_image));

    if ($heroEvent) {
        $heroImage = Str::startsWith($heroEvent->thumbnail, 'thumbnails/') ? asset('storage/'.$heroEvent->thumbnail) : asset('uploads/thumbnails/'.$heroEvent->thumbnail);
        $heroAlt = $heroEvent->thumbnail_alt ?: $heroEvent->title;
    } elseif ($heroCategory?->page?->service_image) {
        $heroImage = Str::contains($heroCategory->page->service_image, '/') ? asset('storage/'.$heroCategory->page->service_image) : asset('uploads/services/'.$heroCategory->page->service_image);
        $heroAlt = $heroCategory->page->service_image_alt ?: $heroCategory->name;
    } elseif ($heroCategory?->page?->banner_image) {
        $heroImage = Str::contains($heroCategory->page->banner_image, '/') ? asset('storage/'.$heroCategory->page->banner_image) : asset('uploads/banners/'.$heroCategory->page->banner_image);
        $heroAlt = $heroCategory->page->banner_alt ?: $heroCategory->name;
    } else {
        $heroImage = null;
        $heroAlt = 'Dịch vụ sự kiện '.$settings['brand_name'];
    }
@endphp



<section class="party-section services-section home-services" id="dich-vu-noi-bat">
    <div class="section-heading">
        <!-- <span>DỊCH VỤ NỔI BẬT</span> -->
        <h1>Chọn dịch vụ cho buổi tiệc của bạn</h1>
        <!-- <p>Khám phá từng dịch vụ, xem hình ảnh thực tế và những mẫu đã thực hiện trước khi liên hệ tư vấn.</p> -->
    </div>
    <div class="service-grid home-service-grid">
        @forelse($categories->take(8) as $i => $cat)
            @php
                $serviceImage = null;
                $serviceAlt = $cat->name;
                if ($cat->page?->service_image) {
                    $serviceImage = Str::contains($cat->page->service_image, '/') ? asset('storage/'.$cat->page->service_image) : asset('uploads/services/'.$cat->page->service_image);
                    $serviceAlt = $cat->page->service_image_alt ?: $cat->name;
                } elseif ($cat->page?->banner_image) {
                    $serviceImage = Str::contains($cat->page->banner_image, '/') ? asset('storage/'.$cat->page->banner_image) : asset('uploads/banners/'.$cat->page->banner_image);
                    $serviceAlt = $cat->page->banner_alt ?: $cat->name;
                }
            @endphp
            <a class="home-service-card tone-{{ ($i % 5) + 1 }}" href="{{ route('category', $cat) }}">
                <div class="home-service-visual">@if($serviceImage)<img src="{{ $serviceImage }}" alt="{{ $serviceAlt }}" loading="lazy">@else<span>{{ $icons[$i] ?? '🎉' }}</span>@endif<b>{{ $cat->events_count }} bài viết</b></div>
                <div class="home-service-body"><span class="service-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span><div><h3>{{ $cat->name }}</h3><p>{{ Str::limit($cat->description ?: 'Xem hình ảnh, thông tin và các mẫu dịch vụ đã thực hiện.', 105) }}</p><strong>Xem dịch vụ <b>→</b></strong></div></div>
            </a>
        @empty
            <div class="empty-state">Các dịch vụ đang được cập nhật. Vui lòng liên hệ để được tư vấn.</div>
        @endforelse
    </div>
    <div class="center"><a class="party-btn outline" href="{{ route('services') }}">Xem toàn bộ dịch vụ</a></div>
</section>

<section class="party-hero home-hero">
    <div class="hero-bubbles"><i></i><i></i><i></i></div>
    <!-- <div class="hero-copy">
        <span class="eyebrow">🎉 Tổ chức ngày vui theo cách của bạn</span>
        <h1>Ngày vui rực rỡ,<br><em>kỷ niệm thật lâu</em></h1>
        <p>{{ $settings['about'] }}</p>
        <div class="hero-buttons"><a class="party-btn primary" href="#dich-vu-noi-bat">Khám phá dịch vụ <span>→</span></a><a class="party-btn light" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ Gọi tư vấn {{ $settings['phone'] }}</a></div>
        <div class="trust-row"><span>✓ Hình ảnh thực tế</span><span>✓ Dịch vụ đa dạng</span><span>✓ Tư vấn trực tiếp</span></div>
    </div> -->
    <div class="hero-showcase">
        @if($heroImage)<img src="{{ $heroImage }}" alt="{{ $heroAlt }}" fetchpriority="high">@else<div class="hero-image-fallback">🎈</div>@endif
        <div class="hero-photo-shade"></div>
        <div class="hero-photo-caption"><span>Ảnh thực tế</span><strong>Không gian tiệc được chăm chút từ những chi tiết nhỏ</strong></div>
        <div class="hero-rating"><b>♥</b><span><strong>Tận tâm & sáng tạo</strong><small>Cho từng khoảnh khắc đáng nhớ</small></span></div>
    </div>
</section>

@if($categories->isNotEmpty())
<nav class="hero-service-strip" aria-label="Dịch vụ nổi bật">
    @foreach($categories->take(4) as $i => $cat)
        <a href="{{ route('category', $cat) }}"><span>{{ $icons[$i] ?? '🎉' }}</span><div><small>Dịch vụ</small><strong>{{ $cat->name }}</strong></div><b>→</b></a>
    @endforeach
</nav>
@endif

<section class="party-section why-section">
    <div class="why-photo"><div class="photo-placeholder">🎊<span>Niềm vui của khách hàng<br>là điều quan trọng nhất</span></div></div>
    <div class="why-copy"><span class="eyebrow">VÌ SAO CHỌN CHÚNG TÔI?</span><h2>Dịch vụ linh hoạt cho từng dịp đặc biệt</h2><p>{{ $settings['tagline'] }}. Bạn chỉ cần xem các mẫu đã thực hiện, chọn dịch vụ quan tâm rồi gọi điện hoặc nhắn Facebook.</p><div class="benefit-list"><div><b>01</b><span><strong>Nhiều nhóm dịch vụ</strong><small>Trang trí, biểu diễn, ăn uống và âm nhạc.</small></span></div><div><b>02</b><span><strong>Xem hình ảnh thực tế</strong><small>Bài đăng từ admin hiển thị trực tiếp cho khách.</small></span></div><div><b>03</b><span><strong>Tư vấn trực tiếp</strong><small>Không cần tạo tài khoản hoặc đặt lịch online.</small></span></div></div></div>
</section>

<section class="party-section posts-section"><div class="section-heading left"><span>HÌNH ẢNH & BÀI VIẾT</span><h2>Các sự kiện và mẫu mới nhất</h2></div><div class="post-grid">@forelse($latest->take(6) as $event)<x-event-card :event="$event"/>@empty<div class="empty-state">Chưa có bài viết. Admin hãy đăng bài đầu tiên để nội dung xuất hiện ở đây.</div>@endforelse</div><div class="center"><a class="party-btn outline" href="{{ route('events') }}">Xem tất cả bài viết</a></div></section>

<section class="contact-banner"><div><span>BẠN ĐANG CHUẨN BỊ MỘT NGÀY VUI?</span><h2>Liên hệ để được tư vấn dịch vụ phù hợp</h2><p>Trao đổi trực tiếp qua điện thoại, Facebook hoặc fanpage để chọn dịch vụ phù hợp.</p></div><div class="contact-actions"><a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>@if($settings['facebook'])<a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Facebook</a>@endif @if($settings['fanpage'])<a href="{{ $settings['fanpage'] }}" target="_blank" rel="noopener">Fanpage</a>@endif</div></section>
@endsection
