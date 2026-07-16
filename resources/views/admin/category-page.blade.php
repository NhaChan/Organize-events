@extends('layouts.admin')

@section('title', 'Nội dung: '.$category->name)

@section('content')
<style>
    .page-editor{display:grid;grid-template-columns:minmax(0,1fr) 330px;gap:20px;align-items:start}.feature-editor{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.image-preview{width:100%;aspect-ratio:16/8;border:2px dashed var(--border);border-radius:12px;overflow:hidden;display:grid;place-items:center;background:#f8fafc;color:var(--muted)}.image-preview img{width:100%;height:100%;object-fit:cover}.editor-actions{display:flex;gap:10px;justify-content:flex-end;margin-bottom:16px}@media(max-width:960px){.page-editor,.feature-editor{grid-template-columns:1fr}}
</style>

<div class="editor-actions">
    <a class="btn-primary-custom" style="background:#64748b" href="{{ route('admin.categories') }}">← Danh mục</a>
    <a class="btn-primary-custom" style="background:#fb7185" href="{{ route('category', $category) }}" target="_blank">↗ Xem trang</a>
    <button class="btn-primary-custom" form="category-page-form">💾 Lưu nội dung</button>
</div>

<form id="category-page-form" method="post" enctype="multipart/form-data" action="{{ route('admin.categories.page.save', $category) }}" data-confirm="Xác nhận cập nhật nội dung và SEO cho trang dịch vụ này?" data-confirm-title="Cập nhật trang dịch vụ">
    @csrf
    <div class="page-editor">
        <div>
            <section class="section-card">
                <h2 class="section-title">Tiêu đề và giới thiệu</h2>
                <div class="form-group"><label class="form-label">Tiêu đề trang</label><input class="form-control-custom" name="page_title" value="{{ old('page_title', $page->page_title) }}" placeholder="{{ $category->name }}"></div>
                <div class="form-group"><label class="form-label">Dòng giới thiệu ngắn</label><input class="form-control-custom" name="subtitle" value="{{ old('subtitle', $page->subtitle) }}" maxlength="255"></div>
                <div class="form-group"><label class="form-label">Nội dung mô tả</label><textarea class="form-control-custom textarea" name="description" rows="8">{{ old('description', $page->description) }}</textarea></div>
            </section>

            <section class="section-card">
                <h2 class="section-title">Ba điểm nổi bật</h2>
                <div class="feature-editor">
                    @for($i = 1; $i <= 3; $i++)
                        <div>
                            <div class="form-group"><label class="form-label">Biểu tượng {{ $i }}</label><input class="form-control-custom" name="feat{{ $i }}_icon" value="{{ old('feat'.$i.'_icon', $page->{'feat'.$i.'_icon'}) }}" placeholder="🎁"></div>
                            <div class="form-group"><label class="form-label">Tiêu đề {{ $i }}</label><input class="form-control-custom" name="feat{{ $i }}_title" value="{{ old('feat'.$i.'_title', $page->{'feat'.$i.'_title'}) }}"></div>
                            <div class="form-group"><label class="form-label">Mô tả {{ $i }}</label><textarea class="form-control-custom textarea" name="feat{{ $i }}_desc" rows="3">{{ old('feat'.$i.'_desc', $page->{'feat'.$i.'_desc'}) }}</textarea></div>
                        </div>
                    @endfor
                </div>
            </section>

            <section class="section-card">
                <h2 class="section-title">Nút kêu gọi hành động</h2>
                <div class="form-row">
                    <div class="form-group"><label class="form-label">Nội dung nút</label><input class="form-control-custom" name="cta_text" value="{{ old('cta_text', $page->cta_text) }}" placeholder="Liên hệ ngay"></div>
                    <div class="form-group"><label class="form-label">Đường dẫn</label><input class="form-control-custom" type="url" name="cta_url" value="{{ old('cta_url', $page->cta_url) }}" placeholder="https://..."></div>
                </div>
            </section>
        </div>

        <aside>
            <section class="section-card">
                <h2 class="section-title">Ảnh banner</h2>
                <div class="image-preview">
                    @if($page->banner_image)
                        <img src="{{ Str::contains($page->banner_image, '/') ? asset('storage/'.$page->banner_image) : asset('uploads/banners/'.$page->banner_image) }}" alt="">
                    @else
                        <span>Chưa có banner</span>
                    @endif
                </div>
                <div class="form-group"><input class="form-control-custom" type="file" name="banner_image" accept="image/*"></div>
                <div class="form-group"><label class="form-label">Alt ảnh</label><input class="form-control-custom" name="banner_alt" value="{{ old('banner_alt', $page->banner_alt) }}"></div>
            </section>

            <section class="section-card">
                <h2 class="section-title">Ảnh minh họa dịch vụ</h2>
                <div class="image-preview">
                    @if($page->service_image)
                        <img src="{{ Str::contains($page->service_image, '/') ? asset('storage/'.$page->service_image) : asset('uploads/services/'.$page->service_image) }}" alt="">
                    @else
                        <span>Chưa có ảnh</span>
                    @endif
                </div>
                <div class="form-group"><input class="form-control-custom" type="file" name="service_image" accept="image/*"></div>
                <div class="form-group"><label class="form-label">Alt ảnh</label><input class="form-control-custom" name="service_image_alt" value="{{ old('service_image_alt', $page->service_image_alt) }}"></div>
            </section>
        </aside>
    </div>
</form>
@endsection
