@extends('layouts.site')

@php
    $hasFilters = request()->filled('q') || request()->filled('category');
    $pageNumber = max(1, request()->integer('page', 1));
    $canonicalQuery = ! $hasFilters && $pageNumber > 1 ? ['page' => $pageNumber] : [];
    $selectedCategory = $categories->firstWhere('slug', request('category'));
@endphp

@section('title', 'Bài viết và hình ảnh sự kiện - '.$settings['brand_name'])
@section('description', 'Hình ảnh, mẫu trang trí và bài viết dịch vụ sự kiện của '.$settings['brand_name'])
@section('canonical', \App\Support\SeoUrl::route('events', [], $canonicalQuery))
@if($hasFilters)
    @section('robots', 'noindex,follow,max-image-preview:large')
@endif


@section('content')
<x-navigation-trail :page-label="'Bài viết & hình ảnh'" />
<!-- <section class="inner-hero">
    <span>BÀI VIẾT & HÌNH ẢNH</span>
    <h1>Ý tưởng cho ngày vui</h1>
    <p>Xem các mẫu trang trí, tiết mục và sự kiện chúng tôi đã đăng tải.</p>
</section> -->
<section class="party-section event-listing">
    <section class="event-filter-card" aria-labelledby="event-filter-title">
        <div class="event-filter-heading">
            <div>
                <span>TÌM NHANH NỘI DUNG</span>
                <h2 id="event-filter-title">Bạn đang quan tâm dịch vụ nào?</h2>
            </div>
            <div class="event-result-count">
                <strong>{{ $events->total() }}</strong>
                <span>bài viết phù hợp</span>
            </div>
        </div>

        <form class="event-filters" method="get" action="{{ \App\Support\SeoUrl::route('events') }}">
            <label class="event-filter-search">
                <span class="event-filter-icon" aria-hidden="true">⌕</span>
                <span class="sr-only">Từ khóa tìm kiếm</span>
                <input name="q" value="{{ request('q') }}" placeholder="Nhập tên bài viết hoặc ý tưởng..." autocomplete="off">
            </label>

            <label class="event-filter-select">
                <span class="sr-only">Danh mục dịch vụ</span>
                <select name="category">
                    <option value="">Tất cả dịch vụ</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>
                            {{ $cat->parent ? '- '.$cat->name : $cat->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <button class="event-filter-submit" type="submit">
                <span aria-hidden="true">≡</span> Lọc nội dung
            </button>

            @if($hasFilters)
                <a class="event-filter-reset" href="{{ \App\Support\SeoUrl::route('events') }}">
                    <span aria-hidden="true">↻</span> Đặt lại
                </a>
            @endif
        </form>

        @if($hasFilters)
            <div class="active-filters" aria-label="Bộ lọc đang áp dụng">
                <span>Đang lọc:</span>
                @if(request()->filled('q'))<b>“{{ request('q') }}”</b>@endif
                @if($selectedCategory)<b>{{ $selectedCategory->name }}</b>@endif
            </div>
        @endif
    </section>

    <div class="post-grid event-results">
        @forelse($events as $event)
            <x-event-card :event="$event" />
        @empty
            <div class="empty-state">
                Không tìm thấy bài viết phù hợp. Hãy thử từ khóa khác hoặc
                <a href="{{ \App\Support\SeoUrl::route('events') }}">đặt lại bộ lọc</a>.
            </div>
        @endforelse
    </div>
    @if($events->hasPages())
        <nav class="category-pagination" aria-label="Phân trang bài viết và hình ảnh">
            @if($events->onFirstPage())
                <span class="disabled" aria-disabled="true">← Trang trước</span>
            @else
                <a href="{{ $events->previousPageUrl() }}" rel="prev">← Trang trước</a>
            @endif
            <span class="page-status">Trang {{ $events->currentPage() }} / {{ $events->lastPage() }}</span>
            @if($events->hasMorePages())
                <a href="{{ $events->nextPageUrl() }}" rel="next">Trang sau →</a>
            @else
                <span class="disabled" aria-disabled="true">Trang sau →</span>
            @endif
        </nav>
    @endif
</section>
@endsection
