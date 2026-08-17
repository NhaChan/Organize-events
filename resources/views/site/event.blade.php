@extends('layouts.site')

@php
    $canonical = \App\Support\SeoUrl::route('event', $event);
    $imagePublicPath = static function (?string $path, string $legacyDirectory): ?string {
        if (blank($path)) {
            return null;
        }

        return Str::contains($path, '/')
            ? 'storage/'.ltrim($path, '/')
            : 'uploads/'.$legacyDirectory.'/'.ltrim($path, '/');
    };
    $thumbnailPath = $imagePublicPath($event->thumbnail, 'thumbnails');
    $thumbnail = $thumbnailPath ? asset($thumbnailPath) : null;
    $thumbnailSeoUrl = $thumbnailPath ? \App\Support\SeoUrl::asset($thumbnailPath) : null;
    $articleImages = collect([$thumbnailSeoUrl])
        ->merge($event->images->map(fn ($image) => \App\Support\SeoUrl::asset($imagePublicPath($image->image_path, 'events'))))
        ->filter()
        ->unique()
        ->values()
        ->all();
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
        'image' => $articleImages,
    ];
@endphp

@section('title', $event->meta_title ?: $event->title.' - '.$settings['brand_name'])
@section('description', $event->meta_description ?: Str::limit($event->summary, 155, ''))
@section('canonical', $canonical)
@section('og_type', 'article')
@if($thumbnailSeoUrl)
    @section('og_image', $thumbnailSeoUrl)
@endif
@push('styles')
@endpush

@section('content')
<script type="application/ld+json">{!! json_encode($articleSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<x-navigation-trail :category="$event->category" :current="$event->title" />
<article class="article">
    <!-- <div class="article-category">{{ optional($event->category)->name }}</div> -->
    <h1>{{ $event->title }}</h1>
    <p class="meta">
        Đăng ngày {{ $event->created_at->format('d/m/Y') }}
        @if($event->event_date) · {{ $event->event_date->format('d/m/Y H:i') }} @endif
        @if($event->location) · 📍 {{ $event->location }} @endif
        · 👁 {{ $event->view_count }}
    </p>
    @if($thumbnail)
        <img class="cover" src="{{ $thumbnail }}" alt="{{ $event->thumbnail_alt ?: $event->title }}">
    @endif
    @if($event->summary)<p class="lead">{{ $event->summary }}</p>@endif
    @if($event->content)
        <section class="event-content">
            <h2>Thông tin chi tiết về {{ $event->title }}</h2>
            <div class="prose">{!! \App\Support\PostContent::sanitize($event->content) !!}</div>
        </section>
    @endif
    @if($event->images->isNotEmpty())
        <section class="event-gallery">
            <!-- <h2>Hình ảnh {{ $event->title }}</h2> -->
            @foreach($event->images as $img)
                <figure>
                    @if($img->title)<h3>{{ $img->title }}</h3>@endif
                    <img class="cover" loading="lazy" src="{{ asset($imagePublicPath($img->image_path, 'events')) }}" alt="{{ $img->alt_text ?: $img->title ?: $event->title }}">
                    @if($img->content)<div class="event-image-content">{!! \App\Support\PostContent::sanitize($img->content) !!}</div>@endif
                </figure>
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
        @if($settings['facebook'])
            <a class="btn-secondary" href="{{ $settings['facebook'] }}" target="_blank" rel="noopener">Nhắn Facebook</a>
        @endif
    </div>
</article>
@endsection
