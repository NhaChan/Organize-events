@extends('layouts.admin')

@section('title', 'Nội dung: '.$category->name)

@section('content')
@php
    $submittedBlocks = old('blocks');
    $editorBlocks = $submittedBlocks !== null
        ? collect($submittedBlocks)->map(function ($data, $key) use ($page) {
            $stored = filled($data['id'] ?? null) ? $page->contentBlocks->firstWhere('id', (int) $data['id']) : null;

            return (object) [
                'key' => $key,
                'id' => $data['id'] ?? null,
                'heading' => $data['heading'] ?? null,
                'content' => $data['content'] ?? null,
                'after_content' => $data['after_content'] ?? null,
                'image' => $stored?->image,
                'image_alt' => $data['image_alt'] ?? null,
                'image_caption' => $data['image_caption'] ?? null,
                'image_fit' => $data['image_fit'] ?? ($stored?->image_fit ?: 'cover'),
                'image_position_y' => $data['image_position_y'] ?? ($stored?->image_position_y ?? 50),
                'remove' => $data['remove'] ?? false,
            ];
        })->values()
        : $page->contentBlocks->values()->map(fn ($block) => (object) [
            'key' => 'existing-'.$block->id,
            'id' => $block->id,
            'heading' => $block->heading,
            'content' => $block->content,
            'after_content' => $block->after_content,
            'image' => $block->image,
            'image_alt' => $block->image_alt,
            'image_caption' => $block->image_caption,
            'image_fit' => $block->image_fit ?: 'cover',
            'image_position_y' => $block->image_position_y ?? 50,
            'remove' => false,
        ]);
@endphp

<div class="category-editor">
    <div class="editor-actions">
        <span class="count-pill" id="category-word-count" data-word-counter data-word-count-scope="#category-page-form" data-word-count-selector='[name="page_title"],[name="category_description"],[name^="feat"][name$="_title"],[name^="feat"][name$="_desc"],[name="cta_text"],[name$="[heading]"],.block-rich-editor' aria-live="polite">✍ Tổng nội dung: 0 từ</span>
        <a class="btn-primary-custom [background:#64748b]" href="{{ route('admin.categories') }}">← Danh mục</a>
        <a class="btn-primary-custom [background:#fb7185]" href="{{ route('category', $category) }}" target="_blank" rel="noopener">↗ Xem trang</a>
        <button class="btn-primary-custom" form="category-page-form">💾 Lưu nội dung</button>
    </div>

    <form id="category-page-form" method="post" enctype="multipart/form-data" action="{{ route('admin.categories.page.save', $category) }}" data-confirm="Xác nhận cập nhật nội dung và SEO cho trang dịch vụ này?" data-confirm-title="Cập nhật trang dịch vụ">
        @csrf

        <section class="section-card">
            <h2 class="section-title">Tiêu đề trang</h2>
            <div class="form-group">
                <label class="form-label">H1 của trang</label>
                <input class="form-control-custom" id="page-title-input" name="page_title" value="{{ old('page_title', $page->page_title) }}" placeholder="{{ $category->name }}">
                <small class="title-counter" id="page-title-counter" aria-live="polite">0 / 60 ký tự</small>
            </div>
            <div class="form-group"><label class="form-label">Meta Description / dòng giới thiệu dưới H1</label><textarea class="form-control-custom textarea" id="category-meta-description" name="category_description" rows="5" data-meta-description aria-describedby="category-meta-description-count" placeholder="Nhập Meta Description hiển thị dưới H1 và trên Google...">{{ old('category_description', $category->description) }}</textarea><div class="char-count" id="category-meta-description-count" data-meta-description-count aria-live="polite">0 ký tự · Khuyến nghị 140–160 ký tự</div><small class="form-help">Nội dung được giữ nguyên đầy đủ và vẫn lưu bình thường khi vượt khuyến nghị. Có thể kéo để mở rộng hoặc thu gọn.</small></div>
        </section>

        <section class="section-card">
            <h2 class="section-title">Hình ảnh trang dịch vụ</h2>
            <p class="section-help">Ảnh thẻ dùng ở danh sách dịch vụ; ảnh banner dùng ở trang chi tiết. Nếu thiếu một ảnh, giao diện sẽ tự dùng ảnh còn lại làm dự phòng.</p>
            <div class="page-image-grid">
                <div class="page-image-field">
                    <div class="page-image-preview">
                        @if($page->service_image)
                            <img src="{{ Str::contains($page->service_image, '/') ? asset('storage/'.$page->service_image) : asset('uploads/services/'.$page->service_image) }}" alt="{{ $page->service_image_alt ?: $category->name }}">
                            <button class="stored-image-delete" type="submit" form="delete-service-image" aria-label="Xóa ảnh thẻ dịch vụ">× Xóa ảnh</button>
                        @else
                            <span>Chưa có ảnh thẻ dịch vụ</span>
                        @endif
                    </div>
                    <div class="form-group"><label class="form-label">Ảnh thẻ dịch vụ (khuyên dùng 4:3)</label><input class="form-control-custom page-image-input" type="file" name="service_image" accept="image/*"></div>
                    <div class="form-group"><label class="form-label">Alt ảnh thẻ</label><input class="form-control-custom" name="service_image_alt" value="{{ old('service_image_alt', $page->service_image_alt) }}" maxlength="255" placeholder="Mô tả ngắn nội dung ảnh"></div>
                    <div class="form-group"><label class="form-label">Chú thích dưới ảnh (tùy chọn)</label><input class="form-control-custom" name="service_image_caption" value="{{ old('service_image_caption', $page->service_image_caption) }}" maxlength="255" placeholder="Hiển thị canh giữa và in nghiêng"></div>
                    <div class="image-display-controls compact"><label>Kiểu hiển thị<select class="form-control-custom" name="service_image_fit"><option value="cover" @selected(old('service_image_fit', $page->service_image_fit ?: 'cover') === 'cover')>Lấp đầy khung</option><option value="contain" @selected(old('service_image_fit', $page->service_image_fit) === 'contain')>Vừa khung, không cắt</option></select></label><label>Căn lên / xuống <span data-position-value>{{ old('service_image_position_y', $page->service_image_position_y ?? 50) }}%</span><input type="range" name="service_image_position_y" min="0" max="100" value="{{ old('service_image_position_y', $page->service_image_position_y ?? 50) }}" data-image-position></label></div>
                </div>
                <div class="page-image-field">
                    <div class="page-image-preview wide">
                        @if($page->banner_image)
                            <img src="{{ Str::contains($page->banner_image, '/') ? asset('storage/'.$page->banner_image) : asset('uploads/banners/'.$page->banner_image) }}" alt="{{ $page->banner_alt ?: $category->name }}">
                            <button class="stored-image-delete" type="submit" form="delete-banner-image" aria-label="Xóa ảnh banner">× Xóa ảnh</button>
                        @else
                            <span>Chưa có ảnh banner</span>
                        @endif
                    </div>
                    <div class="form-group"><label class="form-label">Ảnh banner (khuyên dùng 16:7)</label><input class="form-control-custom page-image-input" type="file" name="banner_image" accept="image/*"></div>
                    <div class="form-group"><label class="form-label">Alt ảnh banner</label><input class="form-control-custom" name="banner_alt" value="{{ old('banner_alt', $page->banner_alt) }}" maxlength="255" placeholder="Mô tả ngắn nội dung ảnh"></div>
                    <div class="form-group"><label class="form-label">Chú thích dưới ảnh (tùy chọn)</label><input class="form-control-custom" name="banner_caption" value="{{ old('banner_caption', $page->banner_caption) }}" maxlength="255" placeholder="Hiển thị canh giữa và in nghiêng"></div>
                    <div class="image-display-controls compact"><label>Kiểu hiển thị<select class="form-control-custom" name="banner_image_fit"><option value="cover" @selected(old('banner_image_fit', $page->banner_image_fit ?: 'cover') === 'cover')>Lấp đầy khung</option><option value="contain" @selected(old('banner_image_fit', $page->banner_image_fit) === 'contain')>Vừa khung, không cắt</option></select></label><label>Căn lên / xuống <span data-position-value>{{ old('banner_image_position_y', $page->banner_image_position_y ?? 50) }}%</span><input type="range" name="banner_image_position_y" min="0" max="100" value="{{ old('banner_image_position_y', $page->banner_image_position_y ?? 50) }}" data-image-position></label></div>
                </div>
            </div>
        </section>

        <section class="section-card">
            <h2 class="section-title">Nội dung linh hoạt dưới bài viết</h2>
            <p class="section-help">Mỗi block hiển thị theo thứ tự H2 → nội dung trước ảnh → ảnh 16:9 → nội dung sau ảnh. Có thể để trống bất kỳ phần nào; block có ảnh bắt buộc nhập Alt.</p>
            <div class="content-blocks" id="content-blocks">
                @foreach($editorBlocks as $index => $block)
                    <article class="content-block {{ $block->remove ? 'removed' : '' }}" data-block data-existing="{{ $block->id ? '1' : '0' }}">
                        <input type="hidden" name="blocks[{{ $block->key }}][id]" value="{{ $block->id }}">
                        <input class="remove-input" type="hidden" name="blocks[{{ $block->key }}][remove]" value="{{ $block->remove ? 1 : 0 }}">
                        <div class="block-head"><strong>Khối nội dung <span class="block-number">{{ $index + 1 }}</span></strong><div class="block-actions"><button class="block-action move-up" type="button" title="Đưa lên">↑</button><button class="block-action move-down" type="button" title="Đưa xuống">↓</button><button class="block-action remove" type="button">Xóa</button></div></div>
                        <div class="block-grid">
                            <div class="block-image-fields">
                                <div class="block-image-preview">
                                    @if($block->image)<img src="{{ Str::contains($block->image, '/') ? asset('storage/'.$block->image) : asset('uploads/services/'.$block->image) }}" alt="{{ $block->image_alt }}"><button class="stored-image-delete" type="submit" form="delete-block-image-{{ $block->id }}" aria-label="Xóa ảnh khỏi khối nội dung">× Xóa ảnh</button>@else<span>Không bắt buộc có ảnh</span>@endif
                                </div>
                                <div class="form-group"><label class="form-label">Ảnh 16:9 (tùy chọn)</label><input class="form-control-custom block-image-input" type="file" name="blocks[{{ $block->key }}][image]" accept="image/*"></div>
                                <div class="form-group"><label class="form-label">Alt ảnh{{ $block->image ? ' *' : '' }}</label><input class="form-control-custom block-alt-input" name="blocks[{{ $block->key }}][image_alt]" value="{{ $block->image_alt }}" maxlength="255" placeholder="Bắt buộc khi block có ảnh" {{ $block->image ? 'required' : '' }}></div>
                                <div class="form-group"><label class="form-label">Chú thích dưới ảnh (tùy chọn)</label><input class="form-control-custom" name="blocks[{{ $block->key }}][image_caption]" value="{{ $block->image_caption }}" maxlength="255" placeholder="Canh giữa và in nghiêng dưới ảnh"></div>
                                <div class="image-display-controls compact"><label>Kiểu hiển thị<select class="form-control-custom" name="blocks[{{ $block->key }}][image_fit]"><option value="cover" @selected($block->image_fit === 'cover')>Lấp đầy khung</option><option value="contain" @selected($block->image_fit === 'contain')>Vừa khung, không cắt</option></select></label><label>Căn lên / xuống <span data-position-value>{{ $block->image_position_y }}%</span><input type="range" name="blocks[{{ $block->key }}][image_position_y]" min="0" max="100" value="{{ $block->image_position_y }}" data-image-position></label></div>
                            </div>
                            <div class="block-fields">
                                <div class="form-group"><label class="form-label">H2 (tùy chọn)</label><input class="form-control-custom" name="blocks[{{ $block->key }}][heading]" value="{{ $block->heading }}" maxlength="255" placeholder="Tiêu đề nội dung"></div>
                                <div class="form-group">
                                    <label class="form-label">Nội dung dưới H2 (tùy chọn)</label>
                                    <div class="block-rich-toolbar"><button class="insert-block-link" type="button">🔗 Chèn liên kết</button><button class="remove-block-link" type="button">Bỏ liên kết</button></div>
                                    <div class="block-rich-editor" contenteditable="true" data-placeholder="Có thể để trống nếu chỉ muốn hiển thị ảnh hoặc H2">{!! \App\Support\PostContent::sanitize($block->content) !!}</div>
                                    <textarea class="block-content-input" name="blocks[{{ $block->key }}][content]" hidden>{{ $block->content }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nội dung sau ảnh (tùy chọn)</label>
                                    <div class="block-rich-toolbar"><button class="insert-block-link" type="button">🔗 Chèn liên kết</button><button class="remove-block-link" type="button">Bỏ liên kết</button></div>
                                    <div class="block-rich-editor" contenteditable="true" data-placeholder="Có thể thêm nội dung tiếp nối sau ảnh">{!! \App\Support\PostContent::sanitize($block->after_content) !!}</div>
                                    <textarea class="block-content-input" name="blocks[{{ $block->key }}][after_content]" hidden>{{ $block->after_content }}</textarea>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <button class="add-block" id="add-content-block" type="button">＋ Thêm H2 / nội dung / ảnh</button>
        </section>

        <section class="section-card">
            <h2 class="section-title">Ba điểm nổi bật (tùy chọn)</h2>
            <div class="form-row">
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
            <h2 class="section-title">Kêu gọi hành động (tùy chọn)</h2>
            <div class="form-row">
                <div class="form-group"><label class="form-label">Nội dung</label><input class="form-control-custom" name="cta_text" value="{{ old('cta_text', $page->cta_text) }}" placeholder="Liên hệ ngay"></div>
                <div class="form-group"><label class="form-label">Đường dẫn</label><input class="form-control-custom" type="url" name="cta_url" value="{{ old('cta_url', $page->cta_url) }}" placeholder="https://..."></div>
            </div>
        </section>
    </form>
    @if($page->service_image)
        <form id="delete-service-image" class="hidden-form" method="post" action="{{ route('admin.categories.page.images.delete', [$category, 'service_image']) }}" data-confirm="Xóa ảnh thẻ dịch vụ?" data-confirm-title="Xác nhận xóa ảnh">@csrf @method('delete')</form>
    @endif
    @if($page->banner_image)
        <form id="delete-banner-image" class="hidden-form" method="post" action="{{ route('admin.categories.page.images.delete', [$category, 'banner_image']) }}" data-confirm="Xóa ảnh banner?" data-confirm-title="Xác nhận xóa ảnh">@csrf @method('delete')</form>
    @endif
    @foreach($editorBlocks->filter(fn ($block) => $block->id && $block->image) as $block)
        <form id="delete-block-image-{{ $block->id }}" class="hidden-form" method="post" action="{{ route('admin.category-content-blocks.image.delete', $block->id) }}" data-confirm="Xóa ảnh khỏi khối nội dung này? Phần chữ vẫn được giữ nguyên." data-confirm-title="Xác nhận xóa ảnh">@csrf @method('delete')</form>
    @endforeach
</div>

<template id="content-block-template">
    <article class="content-block" data-block data-existing="0">
        <input type="hidden" data-name="remove" value="0">
        <div class="block-head"><strong>Khối nội dung <span class="block-number"></span></strong><div class="block-actions"><button class="block-action move-up" type="button" title="Đưa lên">↑</button><button class="block-action move-down" type="button" title="Đưa xuống">↓</button><button class="block-action remove" type="button">Xóa</button></div></div>
        <div class="block-grid">
            <div class="block-image-fields">
                <div class="block-image-preview"><span>Không bắt buộc có ảnh</span></div>
                <div class="form-group"><label class="form-label">Ảnh 16:9 (tùy chọn)</label><input class="form-control-custom block-image-input" type="file" data-name="image" accept="image/*"></div>
                <div class="form-group"><label class="form-label">Alt ảnh</label><input class="form-control-custom block-alt-input" data-name="image_alt" maxlength="255" placeholder="Bắt buộc khi block có ảnh"></div>
                <div class="form-group"><label class="form-label">Chú thích dưới ảnh (tùy chọn)</label><input class="form-control-custom" data-name="image_caption" maxlength="255" placeholder="Canh giữa và in nghiêng dưới ảnh"></div>
                <div class="image-display-controls compact"><label>Kiểu hiển thị<select class="form-control-custom" data-name="image_fit"><option value="cover">Lấp đầy khung</option><option value="contain">Vừa khung, không cắt</option></select></label><label>Căn lên / xuống <span data-position-value>50%</span><input type="range" min="0" max="100" value="50" data-name="image_position_y" data-image-position></label></div>
            </div>
            <div class="block-fields">
                <div class="form-group"><label class="form-label">H2 (tùy chọn)</label><input class="form-control-custom" data-name="heading" maxlength="255" placeholder="Tiêu đề nội dung"></div>
                <div class="form-group">
                    <label class="form-label">Nội dung dưới H2 (tùy chọn)</label>
                    <div class="block-rich-toolbar"><button class="insert-block-link" type="button">🔗 Chèn liên kết</button><button class="remove-block-link" type="button">Bỏ liên kết</button></div>
                    <div class="block-rich-editor" contenteditable="true" data-placeholder="Có thể để trống nếu chỉ muốn hiển thị ảnh hoặc H2"></div>
                    <textarea class="block-content-input" data-name="content" hidden></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Nội dung sau ảnh (tùy chọn)</label>
                    <div class="block-rich-toolbar"><button class="insert-block-link" type="button">🔗 Chèn liên kết</button><button class="remove-block-link" type="button">Bỏ liên kết</button></div>
                    <div class="block-rich-editor" contenteditable="true" data-placeholder="Có thể thêm nội dung tiếp nối sau ảnh"></div>
                    <textarea class="block-content-input" data-name="after_content" hidden></textarea>
                </div>
            </div>
        </div>
    </article>
</template>

<script>
const blocks=document.getElementById('content-blocks');const blockTemplate=document.getElementById('content-block-template');let newBlockIndex=0;const renumberBlocks=()=>{Array.from(blocks.querySelectorAll('[data-block]:not(.removed)')).forEach((block,index)=>block.querySelector('.block-number').textContent=index+1)};const configureImageInput=block=>{const fileInput=block.querySelector('.block-image-input');const altInput=block.querySelector('.block-alt-input');const preview=block.querySelector('.block-image-preview');fileInput?.addEventListener('change',()=>{const file=fileInput.files[0];altInput.required=Boolean(file)||Boolean(preview.querySelector('img'));altInput.closest('.form-group').querySelector('.form-label').textContent=altInput.required?'Alt ảnh *':'Alt ảnh';if(file){preview.innerHTML='<img src="'+URL.createObjectURL(file)+'" alt="Xem trước">';window.updateCategoryImagePreview?.(block.querySelector('.image-display-controls'))}})};const addBlock=()=>{const key='new-'+Date.now()+'-'+newBlockIndex++;const block=blockTemplate.content.firstElementChild.cloneNode(true);block.querySelectorAll('[data-name]').forEach(input=>input.name='blocks['+key+']['+input.dataset.name+']');blocks.append(block);configureImageInput(block);window.bindCategoryImageControls?.(block.querySelector('.image-display-controls'));renumberBlocks();block.scrollIntoView({behavior:'smooth',block:'center'})};document.getElementById('add-content-block').addEventListener('click',addBlock);blocks.addEventListener('click',event=>{const block=event.target.closest('[data-block]');if(!block)return;if(event.target.closest('.move-up')){const previous=block.previousElementSibling;if(previous)blocks.insertBefore(block,previous)}if(event.target.closest('.move-down')){const next=block.nextElementSibling;if(next)blocks.insertBefore(next,block)}if(event.target.closest('.remove')){if(block.dataset.existing==='1'){block.querySelector('.remove-input').value='1';block.querySelectorAll('input:not([type="hidden"]),textarea').forEach(input=>input.disabled=true);block.classList.add('removed')}else block.remove()}renumberBlocks()});blocks.querySelectorAll('[data-block]').forEach(configureImageInput);renumberBlocks();
</script>
<script>
const syncBlockContent = editor => {
    const input = editor.closest('.form-group')?.querySelector('.block-content-input');
    if (input) input.value = editor.innerHTML.trim();
};

window.updateCategoryImagePreview = controls => {
    if (!controls) return;
    const field = controls.closest('.page-image-field,.block-image-fields');
    const previewBox = field?.querySelector('.page-image-preview,.block-image-preview');
    if (previewBox && previewBox.nextElementSibling !== controls) previewBox.after(controls);
    const preview = previewBox?.querySelector('img');
    const fit = controls.querySelector('select')?.value || 'cover';
    const position = controls.querySelector('[data-image-position]')?.value || 50;
    if (preview) {
        preview.style.objectFit = fit;
        preview.style.setProperty('object-position', 'center ' + position + '%', 'important');
    }
    const value = controls.querySelector('[data-position-value]');
    if (value) value.textContent = position + '%';
};

window.bindCategoryImageControls = controls => {
    if (!controls || controls.dataset.livePreviewBound === '1') return;
    controls.dataset.livePreviewBound = '1';
    const update = () => window.updateCategoryImagePreview(controls);
    const range = controls.querySelector('[data-image-position]');
    const select = controls.querySelector('select');
    ['input', 'change', 'pointermove', 'mousemove', 'touchmove', 'keyup'].forEach(type => range?.addEventListener(type, update, { passive: true }));
    ['input', 'change'].forEach(type => select?.addEventListener(type, update));
    update();
};
document.querySelectorAll('.image-display-controls').forEach(window.bindCategoryImageControls);
document.querySelectorAll('.page-image-input').forEach(input => input.addEventListener('change', () => {
    const file = input.files[0];
    if (!file) return;
    const field = input.closest('.page-image-field');
    field.querySelector('.page-image-preview').innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Xem trước">';
    window.updateCategoryImagePreview(field.querySelector('.image-display-controls'));
}));

blocks.addEventListener('input', event => {
    if (event.target.matches('.block-rich-editor')) syncBlockContent(event.target);
});

blocks.addEventListener('mousedown', event => {
    if (event.target.closest('.insert-block-link, .remove-block-link')) event.preventDefault();
});

blocks.addEventListener('click', event => {
    const insertButton = event.target.closest('.insert-block-link');
    const removeButton = event.target.closest('.remove-block-link');
    if (!insertButton && !removeButton) return;

    const editor = event.target.closest('.form-group')?.querySelector('.block-rich-editor');
    const selection = window.getSelection();
    if (!editor || !selection || selection.rangeCount === 0) {
        alert('Hãy bôi đen từ khóa trong nội dung trước.');
        return;
    }

    const range = selection.getRangeAt(0).cloneRange();
    const selectedNode = range.commonAncestorContainer.nodeType === Node.TEXT_NODE
        ? range.commonAncestorContainer.parentElement
        : range.commonAncestorContainer;
    if (!editor.contains(selectedNode) || range.collapsed) {
        alert('Hãy bôi đen từ khóa trong đúng ô nội dung trước.');
        return;
    }

    selection.removeAllRanges();
    selection.addRange(range);

    if (removeButton) {
        document.execCommand('unlink', false);
        syncBlockContent(editor);
        return;
    }

    const href = prompt('Nhập đường dẫn nội bộ (/bai-viet/...) hoặc URL đầy đủ:', '/');
    if (href === null) return;
    const url = href.trim();
    if (!/^(?:https?:\/\/|\/|#)/i.test(url) || url.startsWith('//')) {
        alert('Đường dẫn phải bắt đầu bằng /, #, http:// hoặc https://.');
        return;
    }

    const link = document.createElement('a');
    link.href = url;
    link.appendChild(range.extractContents());
    range.insertNode(link);
    selection.removeAllRanges();
    const afterLink = document.createRange();
    afterLink.selectNodeContents(link);
    selection.addRange(afterLink);
    syncBlockContent(editor);
});

document.getElementById('category-page-form').addEventListener('submit', () => {
    blocks.querySelectorAll('.block-rich-editor').forEach(syncBlockContent);
});

const pageTitleInput = document.getElementById('page-title-input');
const pageTitleCounter = document.getElementById('page-title-counter');
const updatePageTitleCounter = () => {
    const length = Array.from(pageTitleInput.value).length;
    pageTitleCounter.textContent = length + ' / 60 ký tự' + (length > 60 ? ' — vượt ' + (length - 60) + ' ký tự' : '');
    pageTitleCounter.classList.toggle('over-limit', length > 60);
};
pageTitleInput.addEventListener('input', updatePageTitleCounter);
updatePageTitleCounter();

const categoryMetaDescriptionInput = document.getElementById("category-meta-description");
const categoryMetaDescriptionCount = document.getElementById("category-meta-description-count");
const updateCategoryMetaDescriptionCount = () => {
    const length = Array.from(categoryMetaDescriptionInput?.value || "").length;
    const exceeded = Math.max(0, length - 160);
    categoryMetaDescriptionCount.textContent = exceeded
        ? length + " ký tự — vượt khuyến nghị " + exceeded + " ký tự (vẫn có thể lưu)"
        : length + " ký tự · Khuyến nghị 140–160 ký tự";
    categoryMetaDescriptionCount.classList.toggle("over-limit", exceeded > 0);
};
categoryMetaDescriptionInput?.addEventListener("input", updateCategoryMetaDescriptionCount);
updateCategoryMetaDescriptionCount();
</script>
@endsection
