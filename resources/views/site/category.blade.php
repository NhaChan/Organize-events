@extends('layouts.site')

@section('title', ($category->page->page_title ?? $category->name).' — '.$settings['brand_name'])
@section('description', Str::limit($category->description ?: 'Thông tin và hình ảnh dịch vụ '.$category->name, 155, ''))
@section('canonical', route('category', $category))

@section('content')
<x-navigation-trail :category="$category" />
@php($page = $category->page)

@if($page?->banner_image)
<section class="category-banner">
    <img src="{{ Str::contains($page->banner_image, '/') ? asset('storage/'.$page->banner_image) : asset('uploads/banners/'.$page->banner_image) }}" alt="{{ $page->banner_alt ?: $category->name }}">
    <div class="category-banner-shade"></div>
    <div class="category-banner-copy"><span>DỊCH VỤ SỰ KIỆN</span><h1>{{ $page->page_title ?: $category->name }}</h1><p>{{ $page->subtitle ?: $category->description }}</p></div>
</section>
@else
<section class="page-head"><span>DỊCH VỤ SỰ KIỆN</span><h1>{{ $page?->page_title ?: $category->name }}</h1><p>{{ $page?->subtitle ?: $category->description }}</p></section>
@endif

@if($page?->description || $page?->service_image)
<section class="party-section category-intro">
    <div><span class="eyebrow">GIỚI THIỆU DỊCH VỤ</span><h2>{{ $page->page_title ?: $category->name }}</h2><div class="prose">{!! nl2br(e($page->description ?: $category->description)) !!}</div></div>
    @if($page->service_image)<img src="{{ Str::contains($page->service_image, '/') ? asset('storage/'.$page->service_image) : asset('uploads/services/'.$page->service_image) }}" alt="{{ $page->service_image_alt ?: $category->name }}">@endif
</section>
@endif

@if($page && collect([1, 2, 3])->contains(fn ($i) => $page->{'feat'.$i.'_title'}))
<section class="feature-strip">
    @for($i = 1; $i <= 3; $i++)
        @if($page->{'feat'.$i.'_title'})<div><b>{{ $page->{'feat'.$i.'_icon'} ?: '✓' }}</b><h3>{{ $page->{'feat'.$i.'_title'} }}</h3><p>{{ $page->{'feat'.$i.'_desc'} }}</p></div>@endif
    @endfor
</section>
@endif

<section class="wrap">
    @if($category->children->count())<h2>Dịch vụ liên quan</h2><div class="chips">@foreach($category->children as $child)<a href="{{ route('category', $child) }}">{{ $child->name }} →</a>@endforeach</div>@endif
    <h2>Bài viết về {{ $category->name }}</h2>
    <div class="post-grid">@forelse($events as $event)<x-event-card :event="$event" />@empty<div class="empty-state">Dịch vụ này chưa có bài viết. Vui lòng liên hệ để được tư vấn trực tiếp.</div>@endforelse</div>
    {{ $events->links() }}
</section>

@if($page?->cta_text)
<section class="contact-banner compact"><div><span>SẴN SÀNG CHO NGÀY VUI?</span><h2>{{ $page->cta_text }}</h2><p>Trao đổi trực tiếp để nhận tư vấn phù hợp với không gian và ngân sách.</p></div><div class="contact-actions"><a href="{{ $page->cta_url ?: 'tel:'.preg_replace('/\s+/', '', $settings['phone']) }}">Liên hệ ngay</a></div></section>
@endif
@endsection
