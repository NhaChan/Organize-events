@props(['categories'])

@php
    $routeCategory = request()->route('category');
    $routeEvent = request()->route('event');
    $activeCategory = $routeCategory instanceof \App\Models\Category
        ? $routeCategory
        : ($routeEvent instanceof \App\Models\Event ? $routeEvent->category : null);
@endphp

<aside class="service-sidebar" aria-label="Bài viết theo danh mục">
    <div class="service-sidebar-inner">
        <div class="service-sidebar-heading">
            <span>BÀI VIẾT</span>
            <strong>Mới nhất theo danh mục</strong>
            <p>Chọn nhanh bài viết bạn quan tâm</p>
        </div>
        <nav class="service-sidebar-nav">
            @forelse($categories as $category)
                <section class="service-sidebar-group {{ $activeCategory?->id === $category->id ? 'active' : '' }}">
                    <a class="service-sidebar-parent" href="{{ route('category', $category) }}">
                        <span>
                            @if($category->parent)<small>{{ $category->parent->name }}</small>@endif
                            {{ $category->name }}
                        </span>
                        <b><em>{{ $category->published_events_count }}</em>›</b>
                    </a>
                    <div class="service-sidebar-children">
                        @foreach($category->events as $sidebarEvent)
                            @php
                                $sidebarThumbnail = blank($sidebarEvent->thumbnail)
                                    ? null
                                    : (Str::startsWith($sidebarEvent->thumbnail, 'thumbnails/')
                                        ? asset('storage/'.$sidebarEvent->thumbnail)
                                        : asset('uploads/thumbnails/'.$sidebarEvent->thumbnail));
                            @endphp
                            <a class="{{ $routeEvent instanceof \App\Models\Event && $routeEvent->is($sidebarEvent) ? 'active' : '' }}" href="{{ route('event', $sidebarEvent) }}">
                                <span class="service-sidebar-thumb">
                                    @if($sidebarThumbnail)<img src="{{ $sidebarThumbnail }}" alt="" loading="lazy" decoding="async" width="96" height="72">@else<i aria-hidden="true">🎈</i>@endif
                                </span>
                                <span class="service-sidebar-copy">
                                    <span>{{ $sidebarEvent->title }}</span>
                                    <time datetime="{{ $sidebarEvent->created_at->toDateString() }}">{{ $sidebarEvent->created_at->format('d/m/Y') }} · {{ $sidebarEvent->view_count }} lượt xem</time>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @empty
                <p class="service-sidebar-empty">Chưa có bài viết.</p>
            @endforelse
        </nav>
    </div>
</aside>
