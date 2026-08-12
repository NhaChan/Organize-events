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
            'remove' => false,
        ]);
@endphp

<style>
    .category-editor{max-width:980px;margin:0 auto}.editor-actions{display:flex;gap:10px;justify-content:flex-end;margin-bottom:16px;flex-wrap:wrap}.image-preview{width:100%;aspect-ratio:16/9;border:2px dashed var(--border);border-radius:12px;overflow:hidden;display:grid;place-items:center;background:#f8fafc;color:var(--muted)}.image-preview img{width:100%;height:100%;object-fit:cover}.content-blocks{display:grid;gap:16px;margin-top:16px}.content-block{padding:18px;border:1px solid var(--border);border-radius:14px;background:#fff}.content-block.removed{display:none}.block-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}.block-head strong{font-size:.9rem}.block-actions{display:flex;gap:6px}.block-action{border:1px solid var(--border);border-radius:7px;background:#f8fafc;padding:6px 9px;cursor:pointer;color:#475569}.block-action.remove{color:#be123c;background:#fff1f2}.block-grid{display:flex;flex-direction:column;gap:18px}.block-fields{display:contents}.block-fields>.form-group:nth-child(1){order:1}.block-fields>.form-group:nth-child(2){order:2}.block-image-fields{order:3}.block-fields>.form-group:nth-child(3){order:4;margin-bottom:0}.block-image-preview{width:100%;aspect-ratio:16/9;border:1px dashed var(--border);border-radius:10px;display:grid;place-items:center;overflow:hidden;background:#f8fafc;color:var(--muted);font-size:.78rem}.block-image-preview img{width:100%;height:100%;object-fit:cover}.title-counter{display:block;margin-top:6px;color:#64748b;font-size:.75rem}.title-counter.over-limit{color:#dc2626;font-weight:800}.block-rich-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px}.block-rich-toolbar button{border:1px solid var(--border);border-radius:8px;background:#f8fafc;padding:7px 10px;color:#334155;font:inherit;font-size:.78rem;font-weight:700;cursor:pointer}.block-rich-editor{min-height:170px;padding:12px 14px;border:1px solid var(--border);border-radius:9px;background:#fff;line-height:1.7;outline:none}.block-rich-editor:focus{border-color:#fb7185;box-shadow:0 0 0 3px #fb71851f}.block-rich-editor:empty:before{content:attr(data-placeholder);color:#94a3b8}.block-rich-editor a{color:#1677c8;text-decoration:none}.add-block{width:100%;margin-top:14px;padding:13px;border:2px dashed #fda4af;border-radius:12px;background:#fff8fa;color:var(--accent);font:inherit;font-weight:800;cursor:pointer}.section-help{color:var(--muted);font-size:.8rem;line-height:1.6;margin:7px 0 0}@media(max-width:720px){.editor-actions>*{flex:1;justify-content:center}}
</style>

<div class="category-editor">
    <div class="editor-actions">
        <a class="btn-primary-custom" style="background:#64748b" href="{{ route('admin.categories') }}">← Danh mục</a>
        <a class="btn-primary-custom" style="background:#fb7185" href="{{ route('category', $category) }}" target="_blank" rel="noopener">↗ Xem trang</a>
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
            <div class="form-group"><label class="form-label">Dòng giới thiệu ngắn dưới H1</label><input class="form-control-custom" name="subtitle" value="{{ old('subtitle', $page->subtitle) }}" maxlength="255"></div>
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
                                    @if($block->image)<img src="{{ Str::contains($block->image, '/') ? asset('storage/'.$block->image) : asset('uploads/services/'.$block->image) }}" alt="{{ $block->image_alt }}">@else<span>Không bắt buộc có ảnh</span>@endif
                                </div>
                                <div class="form-group"><label class="form-label">Ảnh 16:9 (tùy chọn)</label><input class="form-control-custom block-image-input" type="file" name="blocks[{{ $block->key }}][image]" accept="image/*"></div>
                                <div class="form-group"><label class="form-label">Alt ảnh{{ $block->image ? ' *' : '' }}</label><input class="form-control-custom block-alt-input" name="blocks[{{ $block->key }}][image_alt]" value="{{ $block->image_alt }}" maxlength="255" placeholder="Bắt buộc khi block có ảnh" {{ $block->image ? 'required' : '' }}></div>
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
const blocks=document.getElementById('content-blocks');const blockTemplate=document.getElementById('content-block-template');let newBlockIndex=0;const renumberBlocks=()=>{Array.from(blocks.querySelectorAll('[data-block]:not(.removed)')).forEach((block,index)=>block.querySelector('.block-number').textContent=index+1)};const configureImageInput=block=>{const fileInput=block.querySelector('.block-image-input');const altInput=block.querySelector('.block-alt-input');const preview=block.querySelector('.block-image-preview');fileInput?.addEventListener('change',()=>{const file=fileInput.files[0];altInput.required=Boolean(file)||Boolean(preview.querySelector('img'));altInput.closest('.form-group').querySelector('.form-label').textContent=altInput.required?'Alt ảnh *':'Alt ảnh';if(file)preview.innerHTML='<img src="'+URL.createObjectURL(file)+'" alt="Xem trước">'})};const addBlock=()=>{const key='new-'+Date.now()+'-'+newBlockIndex++;const block=blockTemplate.content.firstElementChild.cloneNode(true);block.querySelectorAll('[data-name]').forEach(input=>input.name='blocks['+key+']['+input.dataset.name+']');blocks.append(block);configureImageInput(block);renumberBlocks();block.scrollIntoView({behavior:'smooth',block:'center'})};document.getElementById('add-content-block').addEventListener('click',addBlock);blocks.addEventListener('click',event=>{const block=event.target.closest('[data-block]');if(!block)return;if(event.target.closest('.move-up')){const previous=block.previousElementSibling;if(previous)blocks.insertBefore(block,previous)}if(event.target.closest('.move-down')){const next=block.nextElementSibling;if(next)blocks.insertBefore(next,block)}if(event.target.closest('.remove')){if(block.dataset.existing==='1'){block.querySelector('.remove-input').value='1';block.querySelectorAll('input:not([type="hidden"]),textarea').forEach(input=>input.disabled=true);block.classList.add('removed')}else block.remove()}renumberBlocks()});blocks.querySelectorAll('[data-block]').forEach(configureImageInput);renumberBlocks();
</script>
<script>
const syncBlockContent = editor => {
    const input = editor.closest('.form-group')?.querySelector('.block-content-input');
    if (input) input.value = editor.innerHTML.trim();
};

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
</script>
@endsection
