@extends('layouts.site')

@section('title', ($category->page->page_title ?? $category->name).' - '.$settings['brand_name'])
@section('description', Str::limit($category->description ?: 'Thông tin và hình ảnh dịch vụ '.$category->name, 155, ''))
@section('canonical', \App\Support\SeoUrl::route('category', $category, request()->integer('page', 1) > 1 ? ['page' => request()->integer('page')] : []))

@section('content')
<x-navigation-trail :category="$category" />
@php($page = $category->page)

<section class="category-hero" aria-label="{{ $category->name }}">
    <span>DỊCH VỤ SỰ KIỆN</span>
    <div class="category-hero-name">{{ $category->name }}</div>
</section>

<section class="wrap category-posts">
    <header class="category-page-heading">
        <h1>{{ $page?->page_title ?: $category->name }}</h1>
        @if($page?->subtitle || $category->description)
            <p>{{ $page?->subtitle ?: $category->description }}</p>
        @endif
    </header>

    @if($category->children->count())
        <h2>Dịch vụ liên quan</h2>
        <div class="chips">@foreach($category->children as $child)<a href="{{ route('category', $child) }}">{{ $child->name }} →</a>@endforeach</div>
    @endif
    <h2>Bài viết về {{ $category->name }}</h2>
    <div class="post-grid">@forelse($events as $event)<x-event-card :event="$event" />@empty<div class="empty-state">Dịch vụ này chưa có bài viết. Vui lòng liên hệ để được tư vấn trực tiếp.</div>@endforelse</div>
    {{ $events->links() }}
</section>

@if(request()->integer('page', 1) === 1)
    @if($page?->contentBlocks->isNotEmpty())
        <section class="category-content-blocks">
            @foreach($page->contentBlocks as $block)
                @continue(blank($block->heading) && blank($block->content) && blank($block->image))
                <article class="category-content-block {{ $block->image ? 'has-image' : 'text-only' }}">
                    @if($block->heading)
                        <h2>{{ $block->heading }}</h2>
                    @endif
                    @if($block->content)
                        <div class="category-block-copy prose">{!! \App\Support\PostContent::paragraphs($block->content) !!}</div>
                    @endif
                    @if($block->image)
                        <img src="{{ Str::contains($block->image, '/') ? asset('storage/'.$block->image) : asset('uploads/services/'.$block->image) }}" alt="{{ $block->image_alt }}" loading="lazy">
                    @endif
                    @if($block->after_content)
                        <div class="category-block-copy prose">{!! \App\Support\PostContent::paragraphs($block->after_content) !!}</div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    @if($page && collect([1, 2, 3])->contains(fn ($i) => $page->{'feat'.$i.'_title'}))
        <section class="feature-strip">
            <h2 class="sr-only">Điểm nổi bật của dịch vụ {{ $category->name }}</h2>
            @for($i = 1; $i <= 3; $i++)
                @if($page->{'feat'.$i.'_title'})<div><b>{{ $page->{'feat'.$i.'_icon'} ?: '✓' }}</b><h3>{{ $page->{'feat'.$i.'_title'} }}</h3><p>{{ $page->{'feat'.$i.'_desc'} }}</p></div>@endif
            @endfor
        </section>
    @endif

    @if($page?->cta_text)
        <section class="contact-banner compact"><div><span>SẴN SÀNG CHO NGÀY VUI?</span><h2>{{ $page->cta_text }}</h2><p>Trao đổi trực tiếp để nhận tư vấn phù hợp với không gian và ngân sách.</p></div><div class="contact-actions"><a href="{{ $page->cta_url ?: 'tel:'.preg_replace('/\s+/', '', $settings['phone']) }}">Liên hệ ngay</a></div></section>
    @endif
@endif
@endsection
