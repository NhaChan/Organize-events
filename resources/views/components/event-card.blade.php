<article class="post-card"><a class="post-image" href="{{ route('event',$event) }}">@if($event->thumbnail)<img src="{{ Str::startsWith($event->thumbnail,'thumbnails/')?asset('storage/'.$event->thumbnail):asset('uploads/thumbnails/'.$event->thumbnail) }}" alt="{{ $event->title }}">@else<span>🎉</span>@endif<div class="post-category">{{ optional($event->category)->name ?: 'Sự kiện' }}</div><span class="post-image-more">Xem bài viết →</span></a>
<div class="post-body"><div class="post-date">{{ optional($event->event_date)->format('d/m/Y') ?: $event->created_at->format('d/m/Y') }} · 👁 {{ $event->view_count }}</div><h3><a href="{{ route('event',$event) }}">{{ $event->title }}</a></h3>
<!-- <p>{{ Str::limit($event->summary,115) }}</p> -->
<x-event-price :event="$event" /></div></article>
