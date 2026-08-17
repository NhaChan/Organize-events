@extends('layouts.site')

@section('title', 'Dịch vụ - '.$settings['brand_name'])
@section("description", "Dịch vụ trang trí, biểu diễn và giải trí sự kiện của ".$settings["brand_name"].". Xem hình ảnh thực tế và liên hệ tư vấn.")
@php
    $pageNumber = max(1, request()->integer('page', 1));
@endphp
@section("canonical", \App\Support\SeoUrl::route("services", [], $pageNumber > 1 ? ['page' => $pageNumber] : []))

@section('content')
<x-navigation-trail page-label="Dịch vụ" />
<!-- <section class="inner-hero"><span>DỊCH VỤ SỰ KIỆN</span><h1>Lựa chọn niềm vui cho buổi tiệc</h1><p>Xem từng nhóm dịch vụ, hình ảnh và bài viết liên quan. Khi cần tư vấn, hãy gọi điện hoặc nhắn Facebook.</p></section> -->
<section class="party-section">
    <div class="section-heading left"><span>DANH MỤC DỊCH VỤ</span><h2>Khám phá dịch vụ phù hợp</h2><p>Chọn nhóm dịch vụ để xem thông tin, hình ảnh thực tế và các bài viết liên quan.</p></div>
    <div class="service-grid service-page-grid">
        @php
            $icons = ['🎈', '🎩', '🤡', '🍭', '🍿', '🌳', '🦫', '🫧', '🎵', '🎤'];
        @endphp
        @forelse($categories as $i => $cat)
            @php
                $cardImage = $cat->page?->service_image ?: $cat->page?->banner_image;
                $legacyDirectory = $cat->page?->service_image ? 'services' : 'banners';
                $cardImageAlt = $cat->page?->service_image
                    ? ($cat->page->service_image_alt ?: $cat->name)
                    : ($cat->page?->banner_alt ?: $cat->name);
            @endphp
            <a class="service-card tone-{{ ($i % 5) + 1 }}" href="{{ route('category', $cat) }}">
                @if($cardImage)
                    <img class="service-image" src="{{ Str::contains($cardImage, '/') ? asset('storage/'.$cardImage) : asset('uploads/'.$legacyDirectory.'/'.$cardImage) }}" alt="{{ $cardImageAlt }}" loading="lazy">
                @else
                    <span class="service-icon">{{ $icons[$i] ?? '🎉' }}</span>
                @endif
                <h3>{{ $cat->name }}</h3><p>{{ Str::limit($cat->description ?: 'Khám phá hình ảnh và nội dung của dịch vụ.', 120) }}</p>
                <div class="service-meta"><span>{{ $cat->events_count }} bài viết</span><strong>Xem chi tiết →</strong></div>
            </a>
        @empty
            <div class="empty-state">Admin chưa tạo dịch vụ nào.</div>
        @endforelse
    </div>
    @if($categories->hasPages())
        <nav class="category-pagination" aria-label="Phân trang dịch vụ">
            @if($categories->onFirstPage())
                <span class="disabled" aria-disabled="true">← Trang trước</span>
            @else
                <a href="{{ $categories->previousPageUrl() }}" rel="prev">← Trang trước</a>
            @endif
            <span class="page-status">Trang {{ $categories->currentPage() }} / {{ $categories->lastPage() }}</span>
            @if($categories->hasMorePages())
                <a href="{{ $categories->nextPageUrl() }}" rel="next">Trang sau →</a>
            @else
                <span class="disabled" aria-disabled="true">Trang sau →</span>
            @endif
        </nav>
    @endif
</section>
<section class="contact-banner compact"><div><h2>Chưa biết chọn dịch vụ nào?</h2><p>Gọi trực tiếp để được tư vấn theo loại tiệc, địa điểm và ngân sách.</p></div><div class="contact-actions"><a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>@if($settings['facebook'])<a href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Nhắn Facebook</a>@endif</div></section>
@endsection
