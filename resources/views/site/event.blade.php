@extends('layouts.site')

@php
    $canonical = \App\Support\SeoUrl::route('event', $event);
    $thumbnail = $event->thumbnail
        ? (Str::startsWith($event->thumbnail, 'thumbnails/')
            ? \App\Support\SeoUrl::asset('storage/'.$event->thumbnail)
            : \App\Support\SeoUrl::asset('uploads/thumbnails/'.$event->thumbnail))
        : null;
    $articleImages = collect([$thumbnail])
        ->merge($event->images->map(fn ($image) => \App\Support\SeoUrl::asset('storage/'.$image->image_path)))
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
@if($thumbnail)
    @section('og_image', $thumbnail)
@endif
@push('styles')
<style>
.article{max-width:920px;padding:32px 24px 64px}
.article>h1{font-size:clamp(1.9rem,4vw,3.1rem);line-height:1.12;letter-spacing:-.025em;margin:0 0 8px}
.article>.meta{margin:0 0 22px;line-height:1.6}
.article h2{font-size:clamp(1.45rem,2.8vw,2rem);line-height:1.25;margin:38px 0 16px}
.article>.cover{margin:14px 0 24px}
.article .event-content .prose a{color:#2563eb!important;text-decoration:none!important;border-bottom:0!important;font-weight:700}
.article .event-content .prose a:hover{color:var(--pink)!important}
.event-content,.event-gallery,.event-followup{margin-top:34px}
.event-gallery{display:grid;gap:24px}
.event-gallery h2{margin-bottom:-4px}
.event-gallery figure{width:100%;margin:0;padding:0}
.event-gallery figure .cover{display:block;width:100%;height:auto;max-height:none;object-fit:contain;margin:0;border-radius:18px}
.event-gallery figure h3{font-size:clamp(1.2rem,2.2vw,1.55rem);margin:0 0 10px}
.event-image-content{color:#46516a;line-height:1.85;margin-top:14px}
.event-image-content a{color:#2563eb!important;text-decoration:none!important;font-weight:700}
.event-followup{padding-top:2px;border-top:1px solid var(--line)}
.event-followup .prose{color:#46516a;line-height:1.85}
@media(max-width:720px){.article{padding:24px 18px 48px}.article>h1{font-size:clamp(1.85rem,9vw,2.35rem)}.article h2{margin-top:30px}}
</style>
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
        <img class="cover" src="{{ $thumbnail }}" alt="{{ $event->title }}">
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
                    <img class="cover" loading="lazy" src="{{ \App\Support\SeoUrl::asset("storage/".$img->image_path) }}" alt="{{ $img->alt_text ?: $img->title ?: $event->title }}">
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
