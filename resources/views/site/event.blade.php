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
    <header class="product-article-heading">
        <h1>{{ $event->title }}</h1>
    </header>

    <section class="product-article-hero {{ $productImages->isEmpty() ? 'without-gallery' : '' }}">
        @if($productImages->isNotEmpty())
            <div class="event-product-gallery" data-product-gallery>
                <div class="event-product-main">
                    <img src="{{ $productImages->first()['src'] }}" alt="{{ $productImages->first()['alt'] }}" data-product-main fetchpriority="high" decoding="async" style="object-fit:{{ $productImages->first()['fit'] }};object-position:center {{ $productImages->first()['position_y'] }}%">
                    @if($productImages->count() > 1)
                        <button class="event-gallery-arrow previous" type="button" data-gallery-previous aria-label="Xem ảnh trước"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
                        <button class="event-gallery-arrow next" type="button" data-gallery-next aria-label="Xem ảnh tiếp theo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
                        <span class="event-gallery-counter"><b data-gallery-current>1</b>/{{ $productImages->count() }}</span>
                    @endif
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

        <aside class="product-article-summary" aria-label="Giá và thông tin liên hệ">
            <div class="product-purchase-top">
                <x-event-price :event="$event" class="article-main-price" />
                <a class="product-phone" href="tel:{{ preg_replace('/\s+/', '', $settings['phone']) }}" aria-label="Gọi {{ $settings['phone'] }}">☎ <strong>{{ $settings['phone'] }}</strong></a>
            </div>

            @if(!empty($event->price_details))
                <dl class="product-price-table">
                    @foreach($event->price_details as $row)
                        @continue(blank($row['label'] ?? null) && blank($row['value'] ?? null))
                        <div><dt>{{ $row['label'] ?? '' }}</dt><dd>{{ $row['value'] ?? '' }}</dd></div>
                    @endforeach
                </dl>
            @endif

            @if($event->location)
                <p class="product-meta">{{ $event->location }}</p>
            @endif
        </aside>
    </section>

    @if($event->summary)<div class="product-lead content-copy">{!! \App\Support\PostContent::paragraphs($event->summary) !!}</div>@endif

    @if($event->content)
        <section class="event-content">
            <h2>{{ $event->content_title ?: 'Thông tin chi tiết về '.$event->title }}</h2>
            <div class="prose content-copy">{!! \App\Support\PostContent::paragraphs($event->content) !!}</div>
        </section>
    @endif

    @if($event->images->contains(fn ($image) => filled($image->title) || filled($image->content)))
        <section class="event-image-notes">
            @foreach($event->images as $image)
                @if($image->title || $image->content)
                    <div class="event-image-note">
                        @if($image->title)<h3>{{ $image->title }}</h3>@endif
                        @if($image->content)<div class="prose content-copy">{!! \App\Support\PostContent::paragraphs($image->content) !!}</div>@endif
                    </div>
                @endif
            @endforeach
        </section>
    @endif

    @if($event->after_gallery_content)
        <section class="event-followup">
            <h2>{{ $event->after_gallery_title ?: 'Thông tin bổ sung' }}</h2>
            <div class="prose content-copy">{!! \App\Support\PostContent::paragraphs($event->after_gallery_content) !!}</div>
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
    const current = gallery.querySelector('[data-gallery-current]');
    let activeIndex = 0;

    const showImage = index => {
        activeIndex = (index + thumbnails.length) % thumbnails.length;
        const button = thumbnails[activeIndex];
        mainImage.src = button.dataset.productSrc;
        mainImage.alt = button.dataset.productAlt;
        mainImage.animate(
            [{ opacity: .35, transform: 'scale(.99)' }, { opacity: 1, transform: 'scale(1)' }],
            { duration: 280, easing: 'ease-out' }
        );
        mainImage.style.objectFit = button.dataset.productFit;
        mainImage.style.objectPosition = 'center ' + button.dataset.productPositionY + '%';
        thumbnails.forEach(item => {
            item.classList.toggle('active', item === button);
            item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
        });
        button.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        if (current) current.textContent = activeIndex + 1;
    };

    thumbnails.forEach((button, index) => button.addEventListener('click', () => showImage(index)));
    gallery.querySelector('[data-gallery-previous]')?.addEventListener('click', () => showImage(activeIndex - 1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => showImage(activeIndex + 1));

    let swipeStartX = null;
    mainImage.addEventListener('pointerdown', event => {
        swipeStartX = event.clientX;
    });
    mainImage.addEventListener('pointerup', event => {
        if (swipeStartX === null) return;
        const distance = event.clientX - swipeStartX;
        swipeStartX = null;
        if (Math.abs(distance) < 45) return;
        showImage(activeIndex + (distance < 0 ? 1 : -1));
    });
    mainImage.addEventListener('pointercancel', () => swipeStartX = null);
});
</script>
@endsection
