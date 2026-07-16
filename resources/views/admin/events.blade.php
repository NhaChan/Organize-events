@extends('layouts.admin')

@section('title', 'Bài viết SEO')
@section('subtitle', 'Đăng, phân loại và theo dõi nội dung hiển thị trên website')

@section('content')
<div class="admin-page-head">
    <div><span class="admin-kicker">NỘI DUNG SEO</span><h1>Quản lý bài viết</h1><p>Tìm kiếm, lọc và chỉnh sửa nội dung trước khi Google thu thập dữ liệu.</p></div>
    <a class="btn-primary-custom" href="{{ route('admin.events.create') }}">＋ Thêm bài viết</a>
</div>

<section class="section-card filter-card">
    <form class="content-filters" method="get" action="{{ route('admin.events') }}">
        <label class="filter-search"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="Tìm theo tiêu đề bài viết..."></label>
        <select name="status"><option value="">Tất cả trạng thái</option>@foreach(['published'=>'Đã đăng','draft'=>'Bản nháp','archived'=>'Lưu trữ'] as $value=>$label)<option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        <select name="category"><option value="">Tất cả danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select>
        <button class="btn-primary-custom">Lọc nội dung</button>
        @if(request()->hasAny(['q','status','category']))<a class="btn-reset-filter" href="{{ route('admin.events') }}">Đặt lại</a>@endif
    </form>
</section>

<section class="section-card post-list-card">
    <div class="section-header"><div><h2 class="section-title">Danh sách bài viết</h2><p class="section-note">{{ $events->total() }} bài viết được tìm thấy</p></div><span class="count-pill">Trang {{ $events->currentPage() }}/{{ max(1, $events->lastPage()) }}</span></div>
    <div class="table-scroll">
        <table class="custom-table post-table">
            <thead><tr><th>Bài viết</th><th>Danh mục</th><th>Ngày sự kiện</th><th>Trạng thái</th><th>Lượt xem</th><th>Thao tác</th></tr></thead>
            <tbody>
            @forelse($events as $event)
                <tr>
                    <td class="post-main-cell"><a class="post-title-link" href="{{ route('admin.events.edit', $event) }}">{{ $event->title }}</a><small class="slug">/bai-viet/{{ $event->slug }}</small><small class="post-updated">Cập nhật {{ $event->updated_at->format('d/m/Y H:i') }}</small></td>
                    <td><span class="cat-badge default">{{ optional($event->category)->name ?: 'Chưa phân loại' }}</span></td>
                    <td>{{ optional($event->event_date)->format('d/m/Y H:i') ?: '—' }}</td>
                    <td><span class="status-badge {{ $event->status }}">{{ ['published'=>'Đã đăng','draft'=>'Bản nháp','archived'=>'Lưu trữ'][$event->status] ?? $event->status }}</span></td>
                    <td><span class="view-count">◉ {{ number_format($event->view_count) }}</span></td>
                    <td><div class="action-btns"><a class="act-btn edit" href="{{ route('admin.events.edit', $event) }}" title="Chỉnh sửa">✎</a>@if($event->status === 'published')<a class="act-btn view" href="{{ route('event', $event) }}" target="_blank" rel="noopener" title="Xem bài">↗</a>@endif<form method="post" action="{{ route('admin.events.delete', $event) }}" data-confirm="Xóa bài viết này? Hành động này không thể hoàn tác." data-confirm-title="Xác nhận xóa bài">@csrf @method('delete')<button class="act-btn delete" title="Xóa">×</button></form></div></td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="admin-empty"><b>Không tìm thấy bài viết</b><span>Thử thay đổi bộ lọc hoặc tạo một bài viết mới.</span></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($events->hasPages())<nav class="admin-pagination">@if($events->onFirstPage())<span class="disabled">← Trang trước</span>@else<a href="{{ $events->previousPageUrl() }}">← Trang trước</a>@endif<span>Trang {{ $events->currentPage() }} / {{ $events->lastPage() }}</span>@if($events->hasMorePages())<a href="{{ $events->nextPageUrl() }}">Trang sau →</a>@else<span class="disabled">Trang sau →</span>@endif</nav>@endif
</section>
@endsection
