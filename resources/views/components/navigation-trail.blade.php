@props(['category' => null, 'current' => null, 'pageLabel' => null])

@php
    $trail = [];
    $node = $category ?? null;
    $guard = 0;
    while ($node && $guard++ < 10) {
        array_unshift($trail, $node);
        $node = $node->parent;
    }
@endphp
<div class="navigation-wrap">
    <!-- <button type="button" class="back-button" onclick="history.back()">← Quay lại</button> -->
    <nav class="breadcrumb" aria-label="Đường dẫn">
        <a href="{{ route('home') }}">Trang chủ</a>
        @if($pageLabel)
            <span>›</span><strong>{{ $pageLabel }}</strong>
        @else
            @if($trail || !empty($current))<span>›</span><a href="{{ route('events') }}">Bài viết</a>@endif
            @foreach($trail as $item)<span>›</span><a href="{{ route('category', $item) }}">{{ $item->name }}</a>@endforeach
            @if(!empty($current))<span>›</span><strong>{{ $current }}</strong>@endif
        @endif
    </nav>
</div>
