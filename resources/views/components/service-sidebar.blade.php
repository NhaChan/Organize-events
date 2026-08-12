@props(['categories'])

@php
    $routeCategory = request()->route('category');
    $routeEvent = request()->route('event');
    $activeCategory = $routeCategory instanceof \App\Models\Category
        ? $routeCategory
        : ($routeEvent instanceof \App\Models\Event ? $routeEvent->category : null);
    $activeIds = collect([$activeCategory?->id, $activeCategory?->parent_id])->filter()->all();
@endphp

<aside class="service-sidebar" aria-label="Danh mục dịch vụ">
    <div class="service-sidebar-inner">
        <div class="service-sidebar-heading">
            <span>KHÁM PHÁ</span>
            <strong>Danh mục dịch vụ</strong>
        </div>
        <nav class="service-sidebar-nav">
            @foreach($categories as $category)
                <section class="service-sidebar-group {{ in_array($category->id, $activeIds, true) ? 'active' : '' }}">
                    <a class="service-sidebar-parent" href="{{ route('category', $category) }}">
                        <span>{{ $category->name }}</span><b>›</b>
                    </a>
                    @if($category->children->isNotEmpty())
                        <div class="service-sidebar-children">
                            @foreach($category->children as $child)
                                <a class="{{ $activeCategory?->id === $child->id ? 'active' : '' }}" href="{{ route('category', $child) }}">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach
        </nav>
    </div>
</aside>
