@extends('layouts.admin') @section('title',$event->exists?'Chỉnh sửa bài viết':'Thêm bài viết mới') @section('content')
@php
    $priceRows = collect(old('price_details', $event->price_details ?? []));
    if ($priceRows->isEmpty()) $priceRows->push(['label' => '', 'value' => '']);
@endphp
<template id="event-price-fields">
    <section class="admin-price-box">
        <h2 class="section-title">Giá và chi tiết giá <small>(tùy chọn)</small></h2>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Giá gốc (VNĐ)</label><input class="form-control-custom" type="number" min="0" step="1000" name="original_price" value="{{ old('original_price',$event->original_price) }}" placeholder="Để trống: Giá liên hệ"></div>
            <div class="form-group"><label class="form-label">Giá giảm (VNĐ)</label><input class="form-control-custom" type="number" min="0" step="1000" name="sale_price" value="{{ old('sale_price',$event->sale_price) }}" placeholder="Không bắt buộc"></div>
        </div>
        <p class="form-help">Các dòng dưới đây sẽ hiển thị thành bảng cạnh H1. Có thể để trống toàn bộ.</p>
        <div class="price-detail-admin-list" id="price-detail-admin-list">
            @foreach($priceRows as $index => $row)
                <div class="price-detail-admin-row" data-price-row>
                    <input class="form-control-custom" name="price_details[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}" maxlength="100" placeholder="Ví dụ: Bao gồm">
                    <input class="form-control-custom" name="price_details[{{ $index }}][value]" value="{{ $row['value'] ?? '' }}" maxlength="255" placeholder="Backdrop, bàn quà, bong bóng...">
                    <button class="image-delete" type="button" data-remove-price-row>×</button>
                </div>
            @endforeach
        </div>
        <button class="add-block compact" id="add-price-detail" type="button">＋ Thêm dòng chi tiết giá</button>
    </section>
</template>
<div class="save-actions"><span class="count-pill" id="article-word-count" data-word-counter data-word-count-scope="#event-form" data-word-count-selector='[name="title"],[name="summary"],[name="content_title"],[name="after_gallery_title"],[name="after_gallery_content"],[name^="price_details"],[name^="existing_image_titles"],[data-extra-title],#content-editor,.js-content-editor' aria-live="polite">✍ Tổng nội dung: 0 từ</span><a class="btn-primary-custom [background:#64748b]" href="{{ route('admin.events') }}">← Danh sách</a><button form="event-form" type="submit" class="btn-primary-custom">💾 Lưu bài viết</button></div>
@if($errors->any())<div class="validation-summary" id="validation-summary"><strong>Vui lòng kiểm tra {{ $errors->count() }} lỗi sau:</strong>@foreach($errors->messages() as $field => $messages)<button type="button" data-error-field="{{ $field }}">• {{ $messages[0] }}</button>@endforeach @if(old('had_thumbnail_upload') || old('had_extra_images_upload'))<p class="[margin:9px_0_0] [font-size:.82rem] [font-weight:800]">⚠ File ảnh không được trình duyệt giữ lại sau khi form báo lỗi. Vui lòng chọn lại {{ old('had_thumbnail_upload') ? 'ảnh chính' : '' }}{{ old('had_thumbnail_upload') && old('had_extra_images_upload') ? ' và ' : '' }}{{ old('had_extra_images_upload') ? 'ảnh phụ' : '' }} trước khi bấm lưu lại.</p>@endif</div>@endif
<form id="event-form" method="post" enctype="multipart/form-data" action="{{ route('admin.events.save',$event->exists?$event:null) }}" data-confirm="Xác nhận lưu nội dung bài viết và các thiết lập SEO?" data-confirm-title="Lưu bài viết">@csrf<input type="hidden" name="had_thumbnail_upload" value="{{ old('had_thumbnail_upload') ? 1 : 0 }}"><input type="hidden" name="had_extra_images_upload" value="{{ old('had_extra_images_upload') ? 1 : 0 }}"><div class="form-grid"><div><div class="tab-btns"><button type="button" class="tab-btn active" data-tab="basic">📝 Cơ bản</button><button type="button" class="tab-btn" data-tab="images">🖼️ Hình ảnh</button><button type="button" class="tab-btn" data-tab="seo">🔍 SEO</button></div>
<section id="tab-basic" class="tab-panel active section-card"><div class="form-group"><label class="form-label">Tiêu đề H1 <span class="required">*</span></label><input class="form-control-custom" name="title" maxlength="255" value="{{ old('title',$event->title) }}" required placeholder="Tên mẫu hoặc chủ đề bài viết..."><small class="form-help">Đây là H1 duy nhất, hiển thị cạnh thư viện ảnh.</small></div><div class="form-group"><label class="form-label">Slug URL</label><div class="input-prefix"><span>/bai-viet/</span><input name="slug" value="{{ old('slug',$event->slug) }}" placeholder="Tự tạo nếu để trống"></div><small class="form-help">Chữ thường, không dấu, phân cách bằng dấu gạch ngang.</small></div><div class="form-group"><label class="form-label">Mô tả ngắn cạnh H1</label><textarea class="form-control-custom textarea" name="summary" rows="5" maxlength="1000" placeholder="Giới thiệu ngắn, điểm nổi bật hoặc thông tin liên hệ...">{{ old('summary',$event->summary) }}</textarea></div><div class="form-group"><label class="form-label">Tiêu đề H2 của nội dung chính</label><input class="form-control-custom" name="content_title" maxlength="255" value="{{ old('content_title',$event->content_title) }}" placeholder="Để trống sẽ tự tạo từ H1"></div><div class="form-group"><label class="form-label">Nội dung dưới H2</label><div class="rich-editor-wrap"><div class="rich-editor-toolbar article-format-toolbar" aria-label="Định dạng nội dung"><button class="editor-tool rich-format" type="button" data-rich-block="p" title="Đoạn văn thường">Đoạn</button><button class="editor-tool rich-format" type="button" data-rich-block="h2" title="Tiêu đề H2 tại vị trí con trỏ">H2</button><button class="editor-tool rich-format" type="button" data-rich-block="h3" title="Tiêu đề H3 tại vị trí con trỏ">H3</button><span class="toolbar-separator"></span><button class="editor-tool rich-format" type="button" data-rich-command="bold" title="Chữ đậm"><strong>B</strong></button><button class="editor-tool rich-format" type="button" data-rich-command="italic" title="Chữ nghiêng"><em>I</em></button><button class="editor-tool rich-format" type="button" data-rich-command="underline" title="Gạch chân"><u>U</u></button><button class="editor-tool rich-format" type="button" data-rich-command="insertUnorderedList" title="Danh sách dấu chấm">• Danh sách</button><button class="editor-tool rich-format" type="button" data-rich-command="insertOrderedList" title="Danh sách đánh số">1. Danh sách</button><span class="toolbar-separator"></span><button class="editor-tool rich-format" type="button" data-rich-font-size="2" title="Chữ nhỏ">A−</button><button class="editor-tool rich-format" type="button" data-rich-font-size="3" title="Chữ thường">A</button><button class="editor-tool rich-format" type="button" data-rich-font-size="4" title="Chữ vừa">A＋</button><button class="editor-tool rich-format" type="button" data-rich-font-size="5" title="Chữ lớn">A＋＋</button><button class="editor-tool rich-format" type="button" data-rich-block="blockquote" title="Trích dẫn">❝ Trích dẫn</button><button class="editor-tool" id="insert-content-image" type="button" title="Chèn ảnh ngay tại vị trí con trỏ">🖼️ Chèn ảnh</button><button class="editor-tool" id="insert-link" type="button">🔗 Chèn liên kết</button><button class="editor-tool" id="remove-link" type="button">Bỏ liên kết</button><button class="editor-tool rich-format" type="button" data-rich-command="removeFormat" title="Xóa định dạng chữ">Xóa định dạng</button></div><div class="rich-editor" id="content-editor" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Nội dung đầy đủ của bài viết...">{!! \App\Support\PostContent::sanitize(old('content',$event->content)) !!}</div></div><textarea id="content-input" name="content" hidden>{{ old('content',$event->content) }}</textarea><input id="content-image-files" type="file" name="content_images[]" accept="image/jpeg,image/png,image/webp,image/gif" multiple hidden><div id="content-image-alt-inputs"></div><small class="link-help">Đặt con trỏ ở dòng cần tạo H2/H3 hoặc chèn ảnh; bôi đen đoạn chữ để đổi cỡ/định dạng. Nhấp đúp vào ảnh trong nội dung để xóa. Font ngoài website vẫn là Roboto.</small></div><div class="form-group"><label class="form-label">Tiêu đề H2 phần bổ sung</label><input class="form-control-custom" name="after_gallery_title" maxlength="255" value="{{ old('after_gallery_title',$event->after_gallery_title) }}" placeholder="Ví dụ: Kinh nghiệm chuẩn bị cho sự kiện"></div><div class="form-group"><label class="form-label">Nội dung phần bổ sung</label><textarea class="form-control-custom textarea" name="after_gallery_content" rows="10" placeholder="Nội dung bổ sung hiển thị cuối bài...">{{ old('after_gallery_content',$event->after_gallery_content) }}</textarea><small class="form-help">Xuống dòng hai lần để tách đoạn; để trống nếu không muốn hiển thị.</small></div><div class="form-row"><div class="form-group"><label class="form-label">Ngày tổ chức</label><input class="form-control-custom" type="datetime-local" name="event_date" value="{{ old('event_date',optional($event->event_date)->format('Y-m-d\TH:i')) }}"></div><div class="form-group"><label class="form-label">Địa điểm</label><input class="form-control-custom" name="location" value="{{ old('location',$event->location) }}" placeholder="TP. Hồ Chí Minh"></div></div></section>
<section id="tab-images" class="tab-panel section-card"><div class="form-group @error('thumbnail') has-error @enderror @error('thumbnail_alt') has-error @enderror"><label class="form-label">Ảnh đại diện</label><div class="thumb-preview" id="thumb-preview">@if($event->thumbnail)<img src="{{ Str::startsWith($event->thumbnail,'thumbnails/')?asset('storage/'.$event->thumbnail):asset('uploads/thumbnails/'.$event->thumbnail) }}" alt="{{ $event->thumbnail_alt ?: $event->title }}">@else<span>📷 Chọn ảnh đại diện</span>@endif
@if($event->exists && $event->thumbnail)<button class="stored-image-delete" type="submit" form="delete-event-thumbnail" aria-label="Xóa ảnh đại diện">× Xóa ảnh</button>@endif</div><input class="form-control-custom [margin-top:10px]" type="file" name="thumbnail" accept="image/*">@error('thumbnail')<small class="field-error">{{ $message }}</small>@enderror<label class="form-label [margin-top:12px]">Alt ảnh chính <span class="required">*</span></label><input class="form-control-custom" name="thumbnail_alt" maxlength="255" value="{{ old('thumbnail_alt',$event->thumbnail_alt) }}" placeholder="Mô tả nội dung ảnh chính dành cho SEO">@error('thumbnail_alt')<small class="field-error">{{ $message }}</small>@enderror<div class="image-display-controls"><label>Kiểu hiển thị<select class="form-control-custom" name="thumbnail_fit"><option value="cover" @selected(old('thumbnail_fit',$event->thumbnail_fit ?: 'cover') === 'cover')>Lấp đầy khung</option><option value="contain" @selected(old('thumbnail_fit',$event->thumbnail_fit) === 'contain')>Vừa khung, không cắt</option></select></label><label>Điều chỉnh lên / xuống <span data-position-value>{{ old('thumbnail_position_y',$event->thumbnail_position_y ?? 50) }}%</span><input type="range" name="thumbnail_position_y" min="0" max="100" value="{{ old('thumbnail_position_y',$event->thumbnail_position_y ?? 50) }}" data-image-position></label></div></div>@if($event->images->count())<div class="form-group"><label class="form-label">Thư viện ảnh hiện có</label><div class="img-gallery">@foreach($event->images as $image)<div class="img-item"><img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $image->alt_text }}"><div class="image-fields"><label>Tiêu đề ảnh</label><input class="image-description" name="existing_image_titles[{{ $image->id }}]" maxlength="255" value="{{ old('existing_image_titles.'.$image->id,$image->title) }}" placeholder="Tiêu đề riêng của ảnh"><label>Nội dung ảnh</label><div class="rich-editor-wrap"><div class="rich-editor-toolbar"><button class="editor-tool js-insert-link" data-editor-id="image-content-{{ $image->id }}" type="button">🔗 Chèn liên kết</button><button class="editor-tool js-remove-link" data-editor-id="image-content-{{ $image->id }}" type="button">Bỏ liên kết</button></div><div class="rich-editor image-rich-editor js-content-editor" id="image-content-{{ $image->id }}" data-input-id="image-content-input-{{ $image->id }}" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Nội dung mô tả riêng cho ảnh...">{!! \App\Support\PostContent::sanitize(old('existing_image_contents.'.$image->id,$image->content)) !!}</div></div><textarea id="image-content-input-{{ $image->id }}" name="existing_image_contents[{{ $image->id }}]" hidden>{{ old('existing_image_contents.'.$image->id,$image->content) }}</textarea><label>Alt ảnh</label><input class="image-description" name="existing_alt_texts[{{ $image->id }}]" maxlength="255" value="{{ old('existing_alt_texts.'.$image->id,$image->alt_text) }}" placeholder="Mô tả Alt dành cho SEO"><div class="image-display-controls compact"><label>Kiểu<select class="image-description" name="existing_image_fits[{{ $image->id }}]"><option value="cover" @selected(old('existing_image_fits.'.$image->id,$image->display_fit ?: 'cover') === 'cover')>Lấp đầy</option><option value="contain" @selected(old('existing_image_fits.'.$image->id,$image->display_fit) === 'contain')>Không cắt</option></select></label><label>Căn dọc <span data-position-value>{{ old('existing_image_positions.'.$image->id,$image->position_y ?? 50) }}%</span><input type="range" name="existing_image_positions[{{ $image->id }}]" min="0" max="100" value="{{ old('existing_image_positions.'.$image->id,$image->position_y ?? 50) }}" data-image-position></label></div><button type="submit" form="delete-image-{{ $image->id }}" class="image-delete">× Xóa ảnh</button></div></div>@endforeach</div></div>@endif<div class="form-group"><label class="form-label">Thêm ảnh phụ</label><div class="extra-images-picker"><input class="extra-images-input" type="file" name="extra_images[]" accept="image/*" multiple><button class="add-images-button" id="add-extra-images" type="button">＋ Thêm ảnh phụ</button><span id="extra-images-count">Chưa chọn ảnh</span></div><small class="form-help">Có thể thêm ảnh ngang hoặc dọc. Sau khi chọn, đặt từng ảnh ở chế độ không cắt hoặc kéo vị trí lên/xuống.</small><div class="new-image-descriptions" id="new-image-descriptions"></div></div></section>
<section id="tab-seo" class="tab-panel section-card"><div class="form-group"><label class="form-label">Meta Title</label><input class="form-control-custom" name="meta_title" maxlength="255" value="{{ old('meta_title',$event->meta_title) }}" placeholder="Để trống sẽ dùng tiêu đề chính"><div class="char-count">Khuyến nghị 50-60 ký tự</div></div><div class="form-group"><label class="form-label">Meta Description</label><textarea class="form-control-custom textarea" id="meta-description" name="meta_description" rows="3" aria-describedby="meta-description-count" placeholder="Mô tả hiển thị trên Google...">{{ old('meta_description',$event->meta_description) }}</textarea><div class="char-count" id="meta-description-count" aria-live="polite">0 ký tự · Khuyến nghị 140–160 ký tự</div></div><div class="[border:1px_solid_var(--border)] [border-radius:10px] [padding:16px] [background:#f8fafc]"><small class="[color:var(--muted)]">🔎 Xem trước đường dẫn Google</small><div class="[color:#1a0dab] [font-size:1rem] [margin-top:8px]">{{ old('meta_title',$event->meta_title) ?: old('title',$event->title) ?: 'Tiêu đề bài viết' }}</div><div class="[color:#15803d] [font-size:.78rem]">{{ url('/bai-viet') }}/{{ old('slug',$event->slug) }}</div><div class="[color:#545454] [font-size:.8rem] [margin-top:4px]">{{ old('meta_description',$event->meta_description) ?: old('summary',$event->summary) }}</div></div></section></div>
<aside><section class="section-card"><h2 class="section-title [margin-bottom:16px]">Cài đặt đăng</h2><div class="form-group"><label class="form-label">Trạng thái</label><select class="form-control-custom" name="status">@foreach(['draft'=>'📄 Bản nháp','published'=>'✅ Đã đăng','archived'=>'🗃️ Lưu trữ'] as $v=>$n)<option value="{{ $v }}" {{ old('status',$event->status?:'draft')===$v?'selected':'' }}>{{ $n }}</option>@endforeach</select></div><div class="form-group"><label class="form-label">Dịch vụ / danh mục</label><select class="form-control-custom" name="category_id"><option value="">- Chọn danh mục -</option>@foreach($categories as $cat)<option value="{{ $cat->id }}" {{ (string)old('category_id',$event->category_id)===(string)$cat->id?'selected':'' }}>{{ $cat->name }}</option>@endforeach</select></div></section><section class="section-card"><h2 class="section-title">Gợi ý SEO</h2><p class="[font-size:.78rem] [color:var(--muted)] [line-height:1.7] [margin-top:10px]">Tiêu đề chứa tên dịch vụ, slug ngắn gọn, ảnh rõ nét và nội dung mô tả chi tiết sẽ giúp Google hiểu bài tốt hơn.</p></section></aside></div></form>
@if($event->exists && $event->thumbnail)<form id="delete-event-thumbnail" method="post" action="{{ route('admin.events.thumbnail.delete', $event) }}" class="hidden-form" data-confirm="Xóa ảnh đại diện khỏi bài viết?" data-confirm-title="Xác nhận xóa ảnh">@csrf @method('delete')</form>@endif
@foreach($event->images as $image)<form id="delete-image-{{ $image->id }}" method="post" action="{{ route('admin.images.delete',$image) }}" class="hidden-form" data-confirm="Xóa ảnh này khỏi bài viết?" data-confirm-title="Xác nhận xóa ảnh">@csrf @method('delete')</form>@endforeach
<dialog class="link-dialog" id="link-dialog"><form class="link-dialog-body" method="dialog" id="link-form"><h2>Gắn liên kết vào từ khóa</h2><p>Từ khóa đã chọn: <span class="selected-keyword" id="selected-keyword"></span></p><div class="form-group"><label class="form-label" for="internal-link">Bài viết nội bộ</label><select class="form-control-custom" id="internal-link"><option value="">— Chọn bài viết —</option>@foreach($linkableEvents as $linkEvent)<option value="{{ route('event', $linkEvent, false) }}">{{ $linkEvent->title }}</option>@endforeach</select></div><div class="form-group"><label class="form-label" for="custom-link">Hoặc nhập đường dẫn</label><input class="form-control-custom" id="custom-link" type="text" inputmode="url" placeholder="/bai-viet/slug hoặc https://..."><small class="form-help">Đường dẫn nhập tay được ưu tiên nếu cả hai trường đều có giá trị.</small></div><div class="link-dialog-actions"><button class="editor-tool" type="button" id="cancel-link">Hủy</button><button class="btn-primary-custom" type="submit">Gắn liên kết</button></div></form></dialog>
<dialog class="link-dialog content-image-dialog" id="content-image-dialog"><form class="link-dialog-body" method="dialog" id="content-image-form"><h2>Chèn ảnh vào nội dung</h2><p>Ảnh nằm đúng tại vị trí con trỏ giữa các đoạn H2/H3 và không được thêm vào thư viện ảnh.</p><div class="form-group"><label class="form-label" for="content-image-picker">Chọn ảnh *</label><input class="form-control-custom" id="content-image-picker" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required></div><div class="form-group"><label class="form-label" for="content-image-alt">Alt ảnh *</label><input class="form-control-custom" id="content-image-alt" maxlength="255" required placeholder="Mô tả nội dung ảnh cho SEO"></div><div class="form-group"><label class="form-label" for="content-image-caption">Chú thích dưới ảnh</label><input class="form-control-custom" id="content-image-caption" maxlength="255" placeholder="Có thể để trống"></div><div class="link-dialog-actions"><button class="editor-tool" type="button" id="cancel-content-image">Hủy</button><button class="btn-primary-custom" type="submit">Chèn ảnh</button></div></form></dialog>
<script>
const priceFieldsTemplate=document.getElementById('event-price-fields');const summaryGroup=document.querySelector('[name="summary"]')?.closest('.form-group');if(priceFieldsTemplate&&summaryGroup)summaryGroup.after(priceFieldsTemplate.content.cloneNode(true));
const priceDetailsList=document.getElementById('price-detail-admin-list');
let priceDetailIndex=priceDetailsList?.querySelectorAll('[data-price-row]').length||0;
document.getElementById('add-price-detail')?.addEventListener('click',()=>{
    const row=document.createElement('div');
    row.className='price-detail-admin-row';
    row.dataset.priceRow='';
    row.innerHTML='<input class="form-control-custom" name="price_details['+priceDetailIndex+'][label]" maxlength="100" placeholder="Ví dụ: Thời gian"><input class="form-control-custom" name="price_details['+priceDetailIndex+'][value]" maxlength="255" placeholder="3–4 giờ"><button class="image-delete" type="button" data-remove-price-row>×</button>';
    priceDetailIndex++;
    priceDetailsList.append(row);
    row.querySelector('input').focus();
});
priceDetailsList?.addEventListener('click',event=>event.target.closest('[data-remove-price-row]')?.closest('[data-price-row]')?.remove());
document.querySelectorAll('.tab-btn').forEach(function(button){button.addEventListener('click',function(){document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));document.querySelectorAll('.tab-panel').forEach(p=>p.classList.remove('active'));button.classList.add('active');document.getElementById('tab-'+button.dataset.tab).classList.add('active')})});
const thumbnailInput=document.querySelector('input[name="thumbnail"]');
const thumbnailPreview=document.getElementById('thumb-preview');
const initialThumbnailPreview=thumbnailPreview?.innerHTML;
thumbnailInput?.addEventListener('change',function(){
    document.querySelector('[name="had_thumbnail_upload"]').value=this.files.length?'1':'0';
    const file=this.files[0];if(!file)return;
    thumbnailPreview.innerHTML='<img src="'+URL.createObjectURL(file)+'" alt="Xem trước"><button class="stored-image-delete" type="button" data-clear-selected-thumbnail>× Xóa ảnh</button>';
    updateImageDisplayPreview(this.closest('.form-group').querySelector('.image-display-controls'));
});
thumbnailPreview?.addEventListener('click',event=>{if(!event.target.closest('[data-clear-selected-thumbnail]'))return;thumbnailInput.value='';document.querySelector('[name="had_thumbnail_upload"]').value='0';thumbnailPreview.innerHTML=initialThumbnailPreview});
const updateImageDisplayPreview=controls=>{
    if(!controls)return;
    const container=controls.closest('.img-item,.new-image-description,.form-group');
    let preview;
    if(container?.matches('.form-group')){
        const previewBox=container.querySelector('.thumb-preview');
        if(previewBox&&previewBox.nextElementSibling!==controls)previewBox.after(controls);
        preview=previewBox?.querySelector('img');
    }else if(container){
        let column=container.querySelector(':scope > .image-preview-column');
        if(!column){column=document.createElement('div');column.className='image-preview-column';container.prepend(column);const image=container.querySelector(':scope > img');if(image)column.append(image)}
        if(controls.parentElement!==column)column.append(controls);
        preview=column.querySelector('img');
    }
    const fit=controls.querySelector('select')?.value||'cover';
    const position=controls.querySelector('[data-image-position]')?.value||50;
    if(preview){preview.style.objectFit=fit;preview.style.objectPosition='center '+position+'%'}
    const value=controls.querySelector('[data-position-value]');if(value)value.textContent=position+'%';
};
const refreshImageDisplayFromEvent=event=>{
    if(event.target.matches('[data-image-position],.image-display-controls select'))updateImageDisplayPreview(event.target.closest('.image-display-controls'));
};
document.addEventListener('input',refreshImageDisplayFromEvent);
document.addEventListener('change',refreshImageDisplayFromEvent);
document.addEventListener('pointermove',event=>{
    if(event.target.matches('[data-image-position]'))updateImageDisplayPreview(event.target.closest('.image-display-controls'));
});
document.querySelectorAll('.image-display-controls').forEach(updateImageDisplayPreview);
const metaDescriptionInput=document.getElementById('meta-description');
const metaDescriptionCount=document.getElementById('meta-description-count');
const updateMetaDescriptionCount=()=>{const length=Array.from(metaDescriptionInput?.value||'').length;const exceeded=Math.max(0,length-160);metaDescriptionCount.textContent=exceeded?length+' ký tự · Vượt khuyến nghị '+exceeded+' ký tự':length+' ký tự · Khuyến nghị 140–160 ký tự';metaDescriptionCount.classList.toggle('over-limit',exceeded>0)};
metaDescriptionInput?.addEventListener('input',updateMetaDescriptionCount);
updateMetaDescriptionCount();
const extraImagesInput=document.querySelector('input[name="extra_images[]"]');
const newImageDescriptions=document.getElementById('new-image-descriptions');
const selectedExtraImages=[];
const extraImagesCount=document.getElementById('extra-images-count');
const syncExtraImagesInput=()=>{const transfer=new DataTransfer();selectedExtraImages.forEach(file=>transfer.items.add(file));extraImagesInput.files=transfer.files;document.querySelector('[name="had_extra_images_upload"]').value=selectedExtraImages.length?'1':'0';extraImagesCount.textContent=selectedExtraImages.length?selectedExtraImages.length+' ảnh đã chọn':'Chưa chọn ảnh'};
const reindexExtraImageRows=()=>{Array.from(newImageDescriptions.children).forEach((row,index)=>{const editorId='new-image-content-'+index;const inputId='new-image-content-input-'+index;row.dataset.index=index;row.querySelector('[data-extra-title]').name='image_titles['+index+']';const editor=row.querySelector('.js-content-editor');editor.id=editorId;editor.dataset.inputId=inputId;row.querySelectorAll('.js-insert-link,.js-remove-link').forEach(button=>button.dataset.editorId=editorId);const hidden=row.querySelector('[data-extra-content]');hidden.id=inputId;hidden.name='image_contents['+index+']';row.querySelector('[data-extra-alt]').name='alt_texts['+index+']';row.querySelector('[data-extra-fit]').name='image_fits['+index+']';row.querySelector('[data-extra-position]').name='image_positions['+index+']';row.querySelector('[data-remove-extra-image]').dataset.index=index})};
const createExtraImageRow=(file,index)=>{const editorId='new-image-content-'+index;const inputId='new-image-content-input-'+index;const row=document.createElement('div');row.className='new-image-description';row.dataset.index=index;const preview=document.createElement('img');preview.src=URL.createObjectURL(file);preview.alt='Xem trước '+file.name;const fields=document.createElement('div');fields.className='image-fields';const name=document.createElement('strong');name.textContent=file.name;const titleLabel=document.createElement('label');titleLabel.textContent='Tiêu đề ảnh';const title=document.createElement('input');title.className='image-description';title.dataset.extraTitle='';title.name='image_titles['+index+']';title.maxLength=255;title.placeholder='Tiêu đề riêng của ảnh';const contentLabel=document.createElement('label');contentLabel.textContent='Nội dung ảnh';const editorWrap=document.createElement('div');editorWrap.className='rich-editor-wrap';const toolbar=document.createElement('div');toolbar.className='rich-editor-toolbar';const insertButton=document.createElement('button');insertButton.type='button';insertButton.className='editor-tool js-insert-link';insertButton.dataset.editorId=editorId;insertButton.textContent='🔗 Chèn liên kết';const removeButton=document.createElement('button');removeButton.type='button';removeButton.className='editor-tool js-remove-link';removeButton.dataset.editorId=editorId;removeButton.textContent='Bỏ liên kết';toolbar.append(insertButton,removeButton);const editor=document.createElement('div');editor.id=editorId;editor.className='rich-editor image-rich-editor js-content-editor';editor.dataset.inputId=inputId;editor.contentEditable='true';editor.setAttribute('role','textbox');editor.setAttribute('aria-multiline','true');editor.dataset.placeholder='Nội dung mô tả riêng cho ảnh...';const hidden=document.createElement('textarea');hidden.id=inputId;hidden.dataset.extraContent='';hidden.name='image_contents['+index+']';hidden.hidden=true;editorWrap.append(toolbar,editor);const altLabel=document.createElement('label');altLabel.textContent='Alt ảnh';const alt=document.createElement('input');alt.className='image-description';alt.dataset.extraAlt='';alt.name='alt_texts['+index+']';alt.maxLength=255;alt.placeholder='Mô tả Alt dành cho SEO';const display=document.createElement('div');display.className='image-display-controls compact';display.innerHTML='<label>Kiểu<select class="image-description" data-extra-fit><option value="cover">Lấp đầy</option><option value="contain">Không cắt</option></select></label><label>Căn dọc <span data-position-value>50%</span><input type="range" min="0" max="100" value="50" data-extra-position data-image-position></label>';const remove=document.createElement('button');remove.type='button';remove.className='image-delete';remove.dataset.removeExtraImage='';remove.dataset.index=index;remove.textContent='× Xóa ảnh đã chọn';fields.append(name,titleLabel,title,contentLabel,editorWrap,hidden,altLabel,alt,display,remove);row.append(preview,fields);return row};
extraImagesInput?.addEventListener('change',function(){const incoming=Array.from(this.files);if(!incoming.length)return;const startIndex=selectedExtraImages.length;selectedExtraImages.push(...incoming);incoming.forEach((file,offset)=>{const row=createExtraImageRow(file,startIndex+offset);newImageDescriptions.append(row);updateImageDisplayPreview(row.querySelector('.image-display-controls'))});reindexExtraImageRows();syncExtraImagesInput()});
document.getElementById('add-extra-images')?.addEventListener('click',()=>extraImagesInput.click());
newImageDescriptions?.addEventListener('click',event=>{const button=event.target.closest('[data-remove-extra-image]');if(!button)return;selectedExtraImages.splice(Number(button.dataset.index),1);button.closest('.new-image-description').remove();reindexExtraImageRows();syncExtraImagesInput()});
const eventForm=document.getElementById('event-form');
const contentEditor=document.getElementById('content-editor');
const linkDialog=document.getElementById('link-dialog');
const internalLink=document.getElementById('internal-link');
const customLink=document.getElementById('custom-link');
const contentImageDialog=document.getElementById("content-image-dialog");
const contentImageForm=document.getElementById("content-image-form");
const contentImagePicker=document.getElementById("content-image-picker");
const contentImageAlt=document.getElementById("content-image-alt");
const contentImageCaption=document.getElementById("content-image-caption");
const contentImageFiles=document.getElementById("content-image-files");
const contentImageAltInputs=document.getElementById("content-image-alt-inputs");
const selectedContentImages=[];
const syncContentImageFiles=()=>{const transfer=new DataTransfer();selectedContentImages.forEach(file=>transfer.items.add(file));contentImageFiles.files=transfer.files};

let selectedRange=null;
let selectedEditor=null;

const basicPanel=document.getElementById('tab-basic');
const imagesPanel=document.getElementById('tab-images');
const thumbnailGroup=document.querySelector('input[name="thumbnail"]')?.closest('.form-group');
const mainContentGroup=contentEditor.closest('.form-group');
const afterTitleGroup=document.querySelector('input[name="after_gallery_title"]')?.closest('.form-group');
const afterContentGroup=document.querySelector('[name="after_gallery_content"]')?.closest('.form-group');
if(thumbnailGroup&&mainContentGroup)mainContentGroup.before(thumbnailGroup);
if(imagesPanel&&afterTitleGroup&&afterContentGroup)imagesPanel.append(afterTitleGroup,afterContentGroup);
document.querySelector('[data-tab="basic"]').textContent='1. H1, H2 & giá';
document.querySelector('[data-tab="images"]').textContent='2. Thư viện ảnh';
document.querySelector('[data-tab="seo"]').textContent='3. SEO';
if(thumbnailGroup){thumbnailGroup.classList.add('content-step');thumbnailGroup.querySelector('.form-label').textContent='Ảnh chính của gallery'}
mainContentGroup.classList.add('content-step');
if(afterTitleGroup){afterTitleGroup.classList.add('content-step');afterTitleGroup.querySelector('.form-label').textContent='Tiêu đề H2 phần bổ sung'}
if(afterContentGroup)afterContentGroup.querySelector('.form-label').textContent='Nội dung phần bổ sung';
const existingGalleryLabel=imagesPanel?.querySelector('.img-gallery')?.closest('.form-group')?.querySelector('.form-label');if(existingGalleryLabel)existingGalleryLabel.textContent='Ảnh thumbnail hiện có';
const extraImagesLabel=document.querySelector('input[name="extra_images[]"]')?.closest('.form-group')?.querySelector('.form-label');if(extraImagesLabel)extraImagesLabel.textContent='Thêm nhiều ảnh vào gallery';

const serverErrorFields=@json(array_keys($errors->messages()));
const findErrorField=field=>{const controls=Array.from(eventForm.elements);const exact=controls.find(control=>control.name===field);if(exact)return exact;const base=field.split('.')[0];return controls.find(control=>control.name===base||control.name===base+'[]'||control.name.startsWith(base+'['))};
const activateErrorField=(field,scroll=true)=>{const control=findErrorField(field);if(!control)return;const group=control.closest('.form-group');group?.classList.add('has-error');const panel=control.closest('.tab-panel');if(panel&&!panel.classList.contains('active'))document.querySelector('[data-tab="'+panel.id.replace('tab-','')+'"]')?.click();const focusTarget=field==='content'?contentEditor:control;if(scroll)window.setTimeout(()=>{group?.scrollIntoView({behavior:'smooth',block:'center'});if(!focusTarget.hidden)focusTarget.focus({preventScroll:true})},80)};
serverErrorFields.forEach(field=>activateErrorField(field,false));
if(serverErrorFields.length)activateErrorField(serverErrorFields[0]);
document.querySelectorAll('[data-error-field]').forEach(button=>button.addEventListener('click',()=>activateErrorField(button.dataset.errorField)));
eventForm.addEventListener('invalid',event=>{const panel=event.target.closest('.tab-panel');if(panel&&!panel.classList.contains('active'))document.querySelector('[data-tab="'+panel.id.replace('tab-','')+'"]')?.click();event.target.closest('.form-group')?.classList.add('has-error')},true);

const allContentEditors=()=>[contentEditor,...document.querySelectorAll('.js-content-editor')];
const rangeIsInEditor=(range,editor)=>range&&editor&&editor.contains(range.startContainer)&&editor.contains(range.endContainer);
const syncEditor=editor=>{const inputId=editor.dataset.inputId||(editor===contentEditor?'content-input':null);const input=inputId?document.getElementById(inputId):null;if(input)input.value=editor.innerHTML};
const rememberSelection=()=>{const selection=window.getSelection();if(!selection.rangeCount)return;const range=selection.getRangeAt(0);const editor=allContentEditors().find(item=>rangeIsInEditor(range,item));if(editor){selectedEditor=editor;selectedRange=range.cloneRange()}};
const restoreSelection=()=>{if(!rangeIsInEditor(selectedRange,selectedEditor))return false;const selection=window.getSelection();selection.removeAllRanges();selection.addRange(selectedRange);return true};

document.addEventListener('selectionchange',rememberSelection);
document.addEventListener('input',event=>{if(event.target.matches('#content-editor,.js-content-editor'))syncEditor(event.target)});
eventForm.addEventListener('submit',()=>allContentEditors().forEach(syncEditor));
document.addEventListener('mousedown',event=>{if(event.target.closest('#insert-content-image,#insert-link,#remove-link,.js-insert-link,.js-remove-link,.rich-format'))event.preventDefault()});
document.addEventListener("click", event => {
    const button = event.target.closest(".rich-format");
    if (! button) return;
    if (selectedEditor !== contentEditor || ! restoreSelection()) {
        alert("Hãy đặt con trỏ hoặc bôi đen nội dung trong ô bài viết trước khi định dạng.");

        return;
    }
    if (button.dataset.richBlock) document.execCommand("formatBlock", false, button.dataset.richBlock);
    if (button.dataset.richCommand) document.execCommand(button.dataset.richCommand, false, null);
    if (button.dataset.richFontSize) document.execCommand("fontSize", false, button.dataset.richFontSize);
    syncEditor(contentEditor);
    contentEditor.focus();
    rememberSelection();
});
const decorateInlineFigure=figure=>{figure.contentEditable="false";figure.title="Bấm đúp để xóa ảnh khỏi nội dung"};
contentEditor.querySelectorAll("figure").forEach(decorateInlineFigure);
document.getElementById("insert-content-image")?.addEventListener("click",()=>{
    if(selectedEditor!==contentEditor||!restoreSelection()){alert("Hãy đặt con trỏ tại vị trí muốn chèn ảnh trong nội dung.");return}
    contentImageForm.reset();
    contentImageDialog.showModal();
});
document.getElementById("cancel-content-image")?.addEventListener("click",()=>contentImageDialog.close());
contentImageForm?.addEventListener("submit",event=>{
    event.preventDefault();
    if(!contentImageForm.reportValidity())return;
    const file=contentImagePicker.files[0];
    if(!file||!restoreSelection()){alert("Không xác định được vị trí chèn ảnh. Hãy đặt lại con trỏ trong nội dung.");return}
    const index=selectedContentImages.length;
    selectedContentImages.push(file);
    syncContentImageFiles();
    const altInput=document.createElement("input");
    altInput.type="hidden";altInput.name="content_image_alts["+index+"]";altInput.value=contentImageAlt.value.trim();contentImageAltInputs.append(altInput);
    const figure=document.createElement("figure");
    figure.dataset.contentImageToken=index;
    const image=document.createElement("img");
    image.src=URL.createObjectURL(file);image.alt=altInput.value;image.dataset.contentImageToken=index;
    figure.append(image);
    const caption=contentImageCaption.value.trim();
    if(caption){const figcaption=document.createElement("figcaption");figcaption.textContent=caption;figure.append(figcaption)}
    decorateInlineFigure(figure);
    const trailing=document.createElement("p");trailing.innerHTML="<br>";
    const anchor=selectedRange.startContainer.nodeType===Node.ELEMENT_NODE?selectedRange.startContainer:selectedRange.startContainer.parentElement;
    const block=anchor?.closest("p,div,h2,h3,blockquote,li");
    if(block&&block!==contentEditor&&contentEditor.contains(block)){block.after(figure,trailing)}else{selectedRange.deleteContents();selectedRange.insertNode(trailing);selectedRange.insertNode(figure)}
    const nextRange=document.createRange();nextRange.selectNodeContents(trailing);nextRange.collapse(true);const selection=window.getSelection();selection.removeAllRanges();selection.addRange(nextRange);selectedRange=nextRange.cloneRange();selectedEditor=contentEditor;
    syncEditor(contentEditor);contentImageDialog.close();contentEditor.focus();
});
contentEditor.addEventListener("dblclick",event=>{const figure=event.target.closest("figure");if(!figure||!contentEditor.contains(figure))return;if(confirm("Xóa ảnh này khỏi nội dung bài viết?")){figure.remove();syncEditor(contentEditor)}});


document.addEventListener('click',event=>{const insertButton=event.target.closest('#insert-link,.js-insert-link');if(insertButton){const editorId=insertButton.dataset.editorId;const expectedEditor=editorId?document.getElementById(editorId):contentEditor;rememberSelection();if(!selectedRange||selectedRange.collapsed||selectedEditor!==expectedEditor||!rangeIsInEditor(selectedRange,selectedEditor)){alert('Hãy bôi đen từ khóa trong đúng ô nội dung trước khi chèn liên kết.');return}document.getElementById('selected-keyword').textContent=selectedRange.toString();internalLink.value='';customLink.value='';customLink.setCustomValidity('');linkDialog.showModal();return}const removeButton=event.target.closest('#remove-link,.js-remove-link');if(removeButton){const editorId=removeButton.dataset.editorId;const expectedEditor=editorId?document.getElementById(editorId):contentEditor;rememberSelection();if(selectedEditor!==expectedEditor||!restoreSelection())return;const anchors=Array.from(selectedEditor.querySelectorAll('a')).filter(anchor=>{try{return selectedRange.intersectsNode(anchor)}catch(error){return false}});anchors.forEach(anchor=>anchor.replaceWith(...anchor.childNodes));syncEditor(selectedEditor);selectedRange=null;selectedEditor.focus()}});

document.getElementById('cancel-link').addEventListener('click',()=>linkDialog.close());
document.getElementById('link-form').addEventListener('submit',event=>{event.preventDefault();const url=customLink.value.trim()||internalLink.value;if(!url){customLink.setCustomValidity('Vui lòng chọn hoặc nhập một đường dẫn.');customLink.reportValidity();return}customLink.setCustomValidity('');if(!restoreSelection()||selectedRange.collapsed){linkDialog.close();alert('Vùng từ khóa đã chọn không còn hợp lệ. Vui lòng bôi đen lại từ khóa.');return}const link=document.createElement('a');link.setAttribute('href',url);link.appendChild(selectedRange.extractContents());selectedRange.insertNode(link);const selection=window.getSelection();selection.removeAllRanges();const afterLink=document.createRange();afterLink.setStartAfter(link);afterLink.collapse(true);selection.addRange(afterLink);selectedRange=afterLink.cloneRange();syncEditor(selectedEditor);linkDialog.close();selectedEditor.focus()});
</script>@endsection
