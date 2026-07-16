@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Tổng quan hiệu quả nội dung và công việc cần làm')

@section('content')
<div class="admin-page-head dashboard-head">
    <div><span class="admin-kicker">TỔNG QUAN WEBSITE</span><h1>Chào {{ auth('admin')->user()->full_name ?: auth('admin')->user()->username }} 👋</h1><p>Theo dõi bài viết, lượt xem và tiếp tục xây dựng nội dung SEO.</p></div>
    <a class="btn-primary-custom" href="{{ route('admin.events.create') }}">＋ Viết bài chuẩn SEO</a>
</div>

<section class="stat-grid dashboard-stats">
    @foreach([
        ['label' => 'Tổng bài viết', 'value' => $counts['events'], 'icon' => '▤', 'tone' => 'pink', 'note' => 'Tất cả trạng thái'],
        ['label' => 'Đã xuất bản', 'value' => $counts['published'], 'icon' => '✓', 'tone' => 'orange', 'note' => 'Google có thể thu thập'],
        ['label' => 'Dịch vụ & danh mục', 'value' => $counts['categories'], 'icon' => '◇', 'tone' => 'purple', 'note' => 'Cấu trúc nội dung'],
        ['label' => 'Tổng lượt xem', 'value' => $counts['views'], 'icon' => '◉', 'tone' => 'green', 'note' => 'Lượt đọc bài viết'],
    ] as $stat)
        <article class="stat-card"><span class="stat-icon {{ $stat['tone'] }}">{{ $stat['icon'] }}</span><div><div class="stat-label">{{ $stat['label'] }}</div><div class="stat-value">{{ number_format($stat['value']) }}</div><div class="stat-note">{{ $stat['note'] }}</div></div></article>
    @endforeach
</section>

<div class="dashboard-grid">
    <section class="section-card recent-posts">
        <div class="section-header"><div><h2 class="section-title">Bài viết gần đây</h2><p class="section-note">Kiểm tra trạng thái trước khi tiếp tục tối ưu.</p></div><a class="text-link" href="{{ route('admin.events') }}">Xem tất cả →</a></div>
        <div class="table-scroll">
            <table class="custom-table">
                <thead><tr><th>Bài viết</th><th>Danh mục</th><th>Trạng thái</th><th>Lượt xem</th><th></th></tr></thead>
                <tbody>
                @forelse($events as $event)
                    <tr>
                        <td><a class="post-title-link" href="{{ route('admin.events.edit', $event) }}">{{ $event->title }}</a><small class="slug">/bai-viet/{{ $event->slug }}</small></td>
                        <td><span class="cat-badge default">{{ optional($event->category)->name ?: 'Chưa phân loại' }}</span></td>
                        <td><span class="status-badge {{ $event->status }}">{{ ['published'=>'Đã đăng','draft'=>'Bản nháp','archived'=>'Lưu trữ'][$event->status] ?? $event->status }}</span></td>
                        <td>{{ number_format($event->view_count) }}</td>
                        <td><a class="act-btn edit" href="{{ route('admin.events.edit', $event) }}" title="Chỉnh sửa">✎</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5"><div class="admin-empty">Chưa có bài viết. Hãy tạo bài đầu tiên để bắt đầu SEO.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <aside>
        <section class="section-card quick-actions"><h2 class="section-title">Thao tác nhanh</h2><a href="{{ route('admin.events.create') }}"><b>＋</b><span><strong>Đăng bài mới</strong><small>Viết nội dung và thiết lập SEO</small></span></a><a href="{{ route('admin.categories') }}"><b>◇</b><span><strong>Quản lý danh mục</strong><small>Xây cấu trúc chủ đề rõ ràng</small></span></a><a href="{{ route('admin.settings') }}"><b>⚙</b><span><strong>Thông tin website</strong><small>Cập nhật thương hiệu và liên hệ</small></span></a></section>
        <section class="section-card seo-checklist"><h2 class="section-title">Checklist trước khi đăng</h2><ul><li>Tiêu đề chứa từ khóa chính</li><li>Slug ngắn và dễ đọc</li><li>Meta description hấp dẫn</li><li>Ảnh rõ nét, có nội dung liên quan</li><li>Chọn đúng dịch vụ/danh mục</li></ul></section>
    </aside>
</div>
@endsection
