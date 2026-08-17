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
        </div>
        <nav class="service-sidebar-nav">
            @forelse($categories as $category)
                <section class="service-sidebar-group {{ $activeCategory?->id === $category->id ? 'active' : '' }}">
                    <a class="service-sidebar-parent" href="{{ route('category', $category) }}">
                        <span>
                            @if($category->parent)<small>{{ $category->parent->name }}</small>@endif
                            {{ $category->name }}
                        </span>
                        <b>›</b>
                    </a>
                    <div class="service-sidebar-children">
                        @foreach($category->events as $sidebarEvent)
                            <a class="{{ $routeEvent instanceof \App\Models\Event && $routeEvent->is($sidebarEvent) ? 'active' : '' }}" href="{{ route('event', $sidebarEvent) }}">
                                <span>{{ $sidebarEvent->title }}</span>
                                <time datetime="{{ $sidebarEvent->created_at->toDateString() }}">{{ $sidebarEvent->created_at->format('d/m/Y') }}</time>
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
