@extends('layouts.site')

@php
    $canonical = \App\Support\SeoUrl::route('event', $event);
    $imagePublicPath = static function (?string $path, string $legacyDirectory): ?string {
        if (blank($path)) return null;

        return Str::contains($path, '/')
            ? 'storage/'.ltrim($path, '/')
            : 'uploads/'.$legacyDirectory.'/'.ltrim($path, '/');
    };
    $thumbnailPath = $imagePublicPath($event->thumbnail, 'thumbnails');
    $thumbnailSeoUrl = $thumbnailPath ? \App\Support\SeoUrl::asset($thumbnailPath) : null;
    $productImages = collect();

    if ($thumbnailPath) {
        $productImages->push([
            'src' => asset($thumbnailPath),
            'seo' => $thumbnailSeoUrl,
            'alt' => $event->thumbnail_alt ?: $event->title,
            'fit' => $event->thumbnail_fit ?: 'cover',
            'position_y' => $event->thumbnail_position_y ?? 50,
        ]);
    }

    foreach ($event->images as $image) {
        $path = $imagePublicPath($image->image_path, 'events');
        $productImages->push([
            'src' => asset($path),
            'seo' => \App\Support\SeoUrl::asset($path),
            'alt' => $image->alt_text ?: $image->title ?: $event->title,
            'fit' => $image->display_fit ?: 'cover',
            'position_y' => $image->position_y ?? 50,
        ]);
    }

    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => $canonical.'#article',
        'headline' => $event->title,
        'description' => $event->meta_description ?: $event->summary,
        'datePublished' => optional($event->created_at)->toAtomString(),
        'dateModified' => optional($event->updated_at)->toAtomString(),
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        'author' => ['@type' => 'Organization', '@id' => \App\Support\SeoUrl::route('home').'#business', 'name' => $settings['brand_name']],
        'publisher' => ['@type' => 'Organization', '@id' => \App\Support\SeoUrl::route('home').'#business', 'name' => $settings['brand_name']],
        'articleSection' => $event->category?->name,
        'image' => $productImages->pluck('seo')->filter()->unique()->values()->all(),
    ];
@endphp

@section('title', $event->meta_title ?: $event->title.' - '.$settings['brand_name'])
@section('description', $event->meta_description ?: Str::limit($event->summary, 155, ''))
@section('canonical', $canonical)
@section('og_type', 'article')
@if($thumbnailSeoUrl) @section('og_image', $thumbnailSeoUrl) @endif

@section('content')
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<x-navigation-trail :category="$event->category" :current="$event->title" />

<article class="article product-article">
    <section class="product-article-hero {{ $productImages->isEmpty() ? 'without-gallery' : '' }}">
        @if($productImages->isNotEmpty())
            <div class="event-product-gallery" data-product-gallery>
                <div class="event-product-main">
                    <img src="{{ $productImages->first()['src'] }}" alt="{{ $productImages->first()['alt'] }}" data-product-main fetchpriority="high" decoding="async" style="object-fit:{{ $productImages->first()['fit'] }};object-position:center {{ $productImages->first()['position_y'] }}%">
                </div>
                @if($productImages->count() > 1)
                    <div class="event-product-thumbnails" aria-label="Chọn ảnh để xem">
                        @foreach($productImages as $index => $image)
                            <button class="event-product-thumb {{ $index === 0 ? 'active' : '' }}" type="button" data-product-src="{{ $image['src'] }}" data-product-alt="{{ $image['alt'] }}" data-product-fit="{{ $image['fit'] }}" data-product-position-y="{{ $image['position_y'] }}" aria-label="Xem ảnh {{ $index + 1 }}" aria-pressed="{{ $index === 0 ? 'true' : 'false' }}">
                                <img src="{{ $image['src'] }}" alt="" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif

        <div class="product-article-summary">
            <h1>{{ $event->title }}</h1>
            <x-event-price :event="$event" class="article-main-price" />
            @if($event->summary)<div class="product-lead">{!! nl2br(e($event->summary)) !!}</div>@endif

            @if(!empty($event->price_details))
                <dl class="product-price-table">
                    @foreach($event->price_details as $row)
                        @continue(blank($row['label'] ?? null) && blank($row['value'] ?? null))
                        <div><dt>{{ $row['label'] ?? '' }}</dt><dd>{{ $row['value'] ?? '' }}</dd></div>
                    @endforeach
                </dl>
            @endif

            <p class="product-meta">
                Cập nhật {{ $event->updated_at->format('d/m/Y') }}
                @if($event->location) · {{ $event->location }} @endif
                · {{ $event->view_count }} lượt xem
            </p>
            <div class="product-quick-contact">
                <strong>Liên hệ tư vấn</strong>
                <a href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ {{ $settings['phone'] }}</a>
            </div>
        </div>
    </section>

    @if($event->content)
        <section class="event-content">
            <h2>{{ $event->content_title ?: 'Thông tin chi tiết về '.$event->title }}</h2>
            <div class="prose">{!! \App\Support\PostContent::sanitize($event->content) !!}</div>
        </section>
    @endif

    @if($event->images->contains(fn ($image) => filled($image->title) || filled($image->content)))
        <section class="event-image-notes">
            @foreach($event->images as $image)
                @if($image->title || $image->content)
                    <div class="event-image-note">
                        @if($image->title)<h3>{{ $image->title }}</h3>@endif
                        @if($image->content)<div class="prose">{!! \App\Support\PostContent::sanitize($image->content) !!}</div>@endif
                    </div>
                @endif
            @endforeach
        </section>
    @endif

    @if($event->after_gallery_content)
        <section class="event-followup">
            <h2>{{ $event->after_gallery_title ?: 'Thông tin bổ sung' }}</h2>
            <div class="prose">{!! nl2br(e($event->after_gallery_content)) !!}</div>
        </section>
    @endif

    <div class="event-actions">
        <a class="btn" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}">☎ Gọi {{ $settings['phone'] }}</a>
        @if($settings['facebook'])<a class="btn-secondary" href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Nhắn Facebook</a>@endif
    </div>
</article>

<script>
document.querySelectorAll('[data-product-gallery]').forEach(gallery => {
    const mainImage = gallery.querySelector('[data-product-main]');
    const thumbnails = Array.from(gallery.querySelectorAll('[data-product-src]'));
    thumbnails.forEach(button => button.addEventListener('click', () => {
        mainImage.src = button.dataset.productSrc;
        mainImage.alt = button.dataset.productAlt;
        mainImage.style.objectFit = button.dataset.productFit;
        mainImage.style.objectPosition = 'center ' + button.dataset.productPositionY + '%';
        thumbnails.forEach(item => {
            item.classList.toggle('active', item === button);
            item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
        });
    }));
});
</script>
@endsection
