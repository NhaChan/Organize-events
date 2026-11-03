@props(['categories'])

@php
    $routeCategory = request()->route('category');
    $routeEvent = request()->route('event');
    $activeCategory = $routeCategory instanceof \App\Models\Category
        ? $routeCategory
        : ($routeEvent instanceof \App\Models\Event ? $routeEvent->category : null);
    $activeRootId = $activeCategory?->parent_id ?: $activeCategory?->id;
@endphp

<aside class="service-sidebar" aria-label="Các dịch vụ theo danh mục">
    <div class="service-sidebar-inner">
        <div class="service-sidebar-heading">
            <strong>Các dịch vụ</strong>
        </div>
        <nav class="service-sidebar-nav">
            @forelse($categories as $category)
                <section class="service-sidebar-group {{ $activeRootId === $category->id ? 'active' : '' }}">
                    <a class="service-sidebar-parent" href="{{ route('category', $category) }}">
                        <span>{{ $category->name }}</span>
                        <span class="service-sidebar-arrow" aria-hidden="true">›</span>
                    </a>
                    @if($category->children->isNotEmpty())
                        <div class="service-sidebar-children">
                            @foreach($category->children as $child)
                                <a class="{{ $activeCategory?->id === $child->id ? 'active' : '' }}" href="{{ route('category', $child) }}">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @empty
                <p class="service-sidebar-empty">Chưa có danh mục dịch vụ.</p>
            @endforelse
        </nav>
    </div>
</aside>
