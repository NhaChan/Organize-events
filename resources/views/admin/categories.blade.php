@extends('layouts.admin')

@section('title', 'Dịch vụ & danh mục')

@section('content')
<div class="admin-page-head">
    <div><span class="admin-kicker">NỘI DUNG WEBSITE</span><h1>Dịch vụ & danh mục</h1><p>Nhóm bài viết theo từng dịch vụ để khách hàng và Google dễ tìm kiếm.</p></div>
    <form method="post" action="{{ route('admin.categories.seed') }}" data-confirm="Tạo thêm các dịch vụ mẫu còn thiếu?" data-confirm-title="Tạo dữ liệu mẫu">@csrf<button class="btn-soft">＋ Tạo dịch vụ mẫu</button></form>
</div>

<div class="admin-grid">
    <section class="section-card">
        <div class="section-header"><h2 class="section-title">Danh sách dịch vụ</h2><span class="count-pill">{{ $categories->count() }} mục</span></div>
        <div class="table-scroll">
            <table class="custom-table">
                <thead><tr><th>Tên dịch vụ</th><th>Nhóm cha</th><th>Bài viết</th><th></th></tr></thead>
                <tbody>
                @foreach($categories as $cat)
                    <tr>
                        <td><strong>{{ $cat->name }}</strong><small class="slug">/dich-vu/{{ $cat->slug }}</small></td>
                        <td><span class="parent-pill">{{ optional($cat->parent)->name ?: 'Dịch vụ chính' }}</span></td>
                        <td>{{ $cat->events_count }}</td>
                        <td><div class="action-btns">
                            <a class="act-btn" href="{{ route('admin.categories.page', $cat) }}" title="Sửa nội dung trang">▤</a>
                            <a class="act-btn edit" href="{{ route('admin.categories', $cat) }}" title="Sửa danh mục">✎</a>
                            <form method="post" action="{{ route('admin.categories.delete', $cat) }}" data-confirm="Xóa dịch vụ này? Hành động này không thể hoàn tác." data-confirm-title="Xác nhận xóa dịch vụ">@csrf @method('delete')<button class="act-btn delete">×</button></form>
                        </div></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="section-card sticky-card">
        <div class="form-card-icon">{{ $edit->exists ? '✎' : '＋' }}</div>
        <h2 class="section-title">{{ $edit->exists ? 'Sửa dịch vụ' : 'Thêm dịch vụ mới' }}</h2>
        <p class="form-help">Slug ngắn, không dấu và chứa từ khóa dịch vụ.</p>
        <form method="post" action="{{ route('admin.categories.save', $edit->exists ? $edit : null) }}" data-confirm="Xác nhận lưu thông tin dịch vụ?" data-confirm-title="Lưu dịch vụ">
            @csrf
            <div class="form-group"><label class="form-label">Tên dịch vụ *</label><input class="form-control-custom" name="name" value="{{ old('name', $edit->name) }}" required placeholder="Ví dụ: Trang trí bong bóng"></div>
            <div class="form-group"><label class="form-label">Slug SEO</label><div class="input-prefix"><span>/dich-vu/</span><input name="slug" value="{{ old('slug', $edit->slug) }}" placeholder="trang-tri-bong-bong"></div></div>
            <div class="form-group"><label class="form-label">Nhóm cha</label><select class="form-control-custom" name="parent_id"><option value="">— Dịch vụ chính —</option>@foreach($categories->whereNull('parent_id')->where('id', '!=', $edit->id) as $parent)<option value="{{ $parent->id }}" {{ (string) old('parent_id', $edit->parent_id) === (string) $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>@endforeach</select></div>
            <div class="form-group"><label class="form-label">Mô tả SEO</label><textarea class="form-control-custom textarea" name="description" placeholder="Giới thiệu ngắn về dịch vụ...">{{ old('description', $edit->description) }}</textarea></div>
            <button class="btn-primary-custom">{{ $edit->exists ? 'Lưu thay đổi' : 'Thêm dịch vụ' }}</button>
            @if($edit->exists)<a class="cancel-link" href="{{ route('admin.categories') }}">Hủy</a>@endif
        </form>
    </section>
</div>
@endsection
