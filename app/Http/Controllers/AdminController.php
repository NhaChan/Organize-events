<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryContentBlock;
use App\Models\CategoryPageImage;
use App\Models\Event;
use App\Models\EventImage;
use App\Support\PostContent;
use App\Support\ImageOptimizer;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'counts' => [
                'events' => Event::count(),
                'published' => Event::where('status', 'published')->count(),
                'categories' => Category::count(),
                'views' => Event::sum('view_count'),
            ],
            'events' => Event::with('category')->latest()->limit(8)->get(),
        ]);
    }

    public function events(Request $request)
    {
        return view('admin.events', [
            'events' => Event::with('category')
                ->when($request->string('q')->trim()->toString(), fn ($query, $keyword) => $query->where('title', 'like', "%{$keyword}%"))
                ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
                ->when($request->integer('category'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function eventForm(?Event $event = null)
    {
        $event ??= new Event;

        return view('admin.event-form', [
            'event' => $event->loadMissing('images'),
            'categories' => Category::with('parent')->orderBy('name')->get(),
            'linkableEvents' => Event::query()
                ->where('status', 'published')
                ->when($event->exists, fn ($query) => $query->where('id', '!=', $event->getKey()))
                ->orderBy('title')
                ->get(['title', 'slug']),
        ]);
    }

    public function saveEvent(Request $request, ?Event $event = null)
    {
        $event ??= new Event;
        $isCreating = ! $event->exists;
        $oldInlineImagePaths = $this->inlineContentImagePaths($event->content);
        $normalizedSlug = filled($request->input('slug'))
            ? Str::slug($request->input('slug'))
            : (filled($request->input('title')) ? Str::slug($request->input('title')) : null);
        $request->merge([
            'slug' => $normalizedSlug,
            'image_contents' => $this->sanitizeContentList($request->input('image_contents')),
            'existing_image_contents' => $this->sanitizeContentList($request->input('existing_image_contents')),
        ]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($event)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'summary' => ['required_if:status,published', 'nullable', 'string', 'max:1000'],
            'content' => ['required_if:status,published', 'nullable', 'string'],
            'content_images' => ['nullable', 'array', 'max:30'],
            'content_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'content_image_alts' => ['nullable', 'array'],
            'content_image_alts.*' => ['required', 'string', 'max:255'],
            'content_title' => ['nullable', 'string', 'max:255'],
            'after_gallery_title' => ['nullable', 'string', 'max:255'],
            'after_gallery_content' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'original_price' => ['nullable', 'integer', 'min:0'],
            'sale_price' => ['nullable', 'integer', 'min:0'],
            'price_details' => ['nullable', 'array', 'max:20'],
            'price_details.*' => ['array:label,value'],
            'price_details.*.label' => ['nullable', 'string', 'max:100'],
            'price_details.*.value' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'thumbnail' => [Rule::requiredIf($request->boolean('had_thumbnail_upload') && blank($event->thumbnail)), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'thumbnail_alt' => [Rule::requiredIf($request->hasFile('thumbnail') || filled($event->thumbnail)), 'nullable', 'string', 'max:255'],
            'thumbnail_fit' => ['nullable', Rule::in(['cover', 'contain'])],
            'thumbnail_position_y' => ['nullable', 'integer', 'between:0,100'],
            'extra_images' => [Rule::requiredIf($request->boolean('had_extra_images_upload')), 'nullable', 'array'],
            'extra_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'alt_texts' => ['nullable', 'array'],
            'alt_texts.*' => ['nullable', 'string', 'max:255'],
            'image_titles' => ['nullable', 'array'],
            'image_titles.*' => ['nullable', 'string', 'max:255'],
            'image_contents' => ['nullable', 'array'],
            'image_contents.*' => ['nullable', 'string'],
            'image_fits' => ['nullable', 'array'],
            'image_fits.*' => ['nullable', Rule::in(['cover', 'contain'])],
            'image_positions' => ['nullable', 'array'],
            'image_positions.*' => ['nullable', 'integer', 'between:0,100'],
            'existing_alt_texts' => ['nullable', 'array'],
            'existing_alt_texts.*' => ['nullable', 'string', 'max:255'],
            'existing_image_titles' => ['nullable', 'array'],
            'existing_image_titles.*' => ['nullable', 'string', 'max:255'],
            'existing_image_contents' => ['nullable', 'array'],
            'existing_image_contents.*' => ['nullable', 'string'],
            'existing_image_fits' => ['nullable', 'array'],
            'existing_image_fits.*' => ['nullable', Rule::in(['cover', 'contain'])],
            'existing_image_positions' => ['nullable', 'array'],
            'existing_image_positions.*' => ['nullable', 'integer', 'between:0,100'],
            'had_thumbnail_upload' => ['nullable', 'boolean'],
            'had_extra_images_upload' => ['nullable', 'boolean'],
        ], [
            'summary.required_if' => 'Tóm tắt là bắt buộc khi trạng thái là Đã đăng.',
            'content.required_if' => 'Nội dung chi tiết là bắt buộc khi trạng thái là Đã đăng.',
            'slug.unique' => 'Slug URL này đã tồn tại. Vui lòng nhập slug khác.',
            'thumbnail_alt.required' => 'Vui lòng nhập Alt cho ảnh chính.',
            'thumbnail.required' => 'Ảnh chính đã bị trình duyệt xóa sau lần báo lỗi. Vui lòng chọn lại ảnh chính.',
            'thumbnail.max' => 'Ảnh chính không được lớn hơn 5MB.',
            'thumbnail.image' => 'Ảnh chính phải là một tệp hình ảnh hợp lệ.',
            'extra_images.required' => 'Ảnh phụ đã bị trình duyệt xóa sau lần báo lỗi. Vui lòng chọn lại ảnh phụ.',
            'extra_images.*.max' => 'Mỗi ảnh phụ không được lớn hơn 5MB.',
        ], [
            'title' => 'Tiêu đề',
            'slug' => 'Slug URL',
            'category_id' => 'Dịch vụ / danh mục',
            'summary' => 'Tóm tắt',
            'content' => 'Nội dung chi tiết',
            'thumbnail' => 'Ảnh chính',
            'thumbnail_alt' => 'Alt ảnh chính',
            'extra_images' => 'Ảnh phụ',
            'alt_texts.*' => 'Alt ảnh phụ',
            'original_price' => 'Giá gốc',
            'sale_price' => 'Giá giảm',
            'meta_title' => 'Meta Title',
            'meta_description' => 'Meta Description',
        ]);

        $data["content"] = $this->storeInlineContentImages($request, $data["content"] ?? null);

        $data['price_details'] = collect($data['price_details'] ?? [])
            ->map(fn (array $row) => [
                'label' => trim($row['label'] ?? ''),
                'value' => trim($row['value'] ?? ''),
            ])
            ->filter(fn (array $row) => $row['label'] !== '' || $row['value'] !== '')
            ->values()
            ->all() ?: null;

        unset($data['content_images'], $data['content_image_alts'], $data['extra_images'], $data['alt_texts'], $data['image_titles'], $data['image_contents'], $data['image_fits'], $data['image_positions'], $data['existing_alt_texts'], $data['existing_image_titles'], $data['existing_image_contents'], $data['existing_image_fits'], $data['existing_image_positions'], $data['had_thumbnail_upload'], $data['had_extra_images_upload']);
        if ($request->hasFile('thumbnail')) {
            $this->removeFile($event->thumbnail);
            $data['thumbnail'] = ImageOptimizer::store($request->file('thumbnail'), 'thumbnails', 1600);
        }

        $event->fill($data)->save();
        foreach (array_diff($oldInlineImagePaths, $this->inlineContentImagePaths($event->content)) as $removedInlineImage) {
            $this->removeFile($removedInlineImage);
        }

        $existingImageIds = collect([
            array_keys((array) $request->input('existing_alt_texts', [])),
            array_keys((array) $request->input('existing_image_titles', [])),
            array_keys((array) $request->input('existing_image_contents', [])),
            array_keys((array) $request->input('existing_image_fits', [])),
            array_keys((array) $request->input('existing_image_positions', [])),
        ])->flatten()->unique();

        foreach ($existingImageIds as $imageId) {
            $title = $request->input("existing_image_titles.{$imageId}");
            $content = $request->input("existing_image_contents.{$imageId}");
            $alt = $request->input("existing_alt_texts.{$imageId}");
            $fit = $request->input("existing_image_fits.{$imageId}", 'cover');
            $position = $request->integer("existing_image_positions.{$imageId}", 50);
            $event->images()->whereKey($imageId)->update([
                'title' => filled($title) ? trim($title) : null,
                'content' => filled($content) ? $content : null,
                'alt_text' => filled($alt) ? trim($alt) : null,
                'display_fit' => $fit,
                'position_y' => $position,
            ]);
        }

        $nextSort = (int) $event->images()->max('sort_order') + 1;

        foreach ((array) $request->file('extra_images', []) as $index => $file) {
            $event->images()->create([
                'image_path' => ImageOptimizer::store($file, 'events'),
                'title' => filled($request->input("image_titles.{$index}"))
                    ? trim($request->input("image_titles.{$index}"))
                    : null,
                'content' => $request->input("image_contents.{$index}"),
                'alt_text' => filled($request->input("alt_texts.{$index}"))
                    ? trim($request->input("alt_texts.{$index}"))
                    : null,
                'display_fit' => $request->input("image_fits.{$index}", 'cover'),
                'position_y' => $request->integer("image_positions.{$index}", 50),
                'sort_order' => $nextSort + $index,
            ]);
        }

        if ($isCreating) {
            return redirect()->route('admin.events.create')->with('success', 'Đã thêm bài viết. Form đã được làm trống để bạn có thể nhập bài mới.');
        }

        return redirect()->route('admin.events.edit', $event)->with('success', 'Đã cập nhật bài viết.');
    }

    private function sanitizeContentList(mixed $contents): mixed
    {
        if (! is_array($contents)) {
            return $contents;

        }
        return array_map(fn ($content) => PostContent::sanitize($content), $contents);
    }

    private function storeInlineContentImages(Request $request, ?string $content): ?string
    {
        $content ??= "";

        foreach ((array) $request->file("content_images", []) as $index => $file) {
            $pattern = "#<img\b[^>]*data-content-image-token=\"".preg_quote((string) $index, "#")."\"[^>]*>#iu";
            if (! preg_match($pattern, $content)) {
                continue;
            }

            $path = ImageOptimizer::store($file, "content-images");
            $alt = trim((string) $request->input("content_image_alts.".$index));
            $replacement = "<img src=\"/storage/".e($path)."\" alt=\"".e($alt)."\" loading=\"lazy\" decoding=\"async\">";
            $content = preg_replace($pattern, $replacement, $content, 1) ?? $content;
        }

        return PostContent::sanitize($content);
    }

    private function inlineContentImagePaths(?string $content): array
    {
        preg_match_all("#/storage/(content-images/[a-zA-Z0-9/_\.-]+)#", $content ?? "", $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    public function deleteEventThumbnail(Event $event)
    {
        $this->removeFile($event->thumbnail);
        $event->forceFill([
            'thumbnail' => null,
            'thumbnail_alt' => null,
        ])->save();

        return redirect()->route('admin.events.edit', $event)->with('success', 'Đã xóa ảnh đại diện.');
    }

    public function deleteImage(EventImage $image)
    {
        $event = $image->event;
        $this->removeFile($image->image_path);
        $image->delete();

        return redirect()->route('admin.events.edit', $event)->with('success', 'Đã xóa ảnh.');
    }

    public function deleteEvent(Event $event)
    {
        $this->removeFile($event->thumbnail);
        foreach ($this->inlineContentImagePaths($event->content) as $inlineImage) { $this->removeFile($inlineImage); }
        $event->load('images')->images->each(fn ($image) => $this->removeFile($image->image_path));
        $event->delete();

        return back()->with('success', 'Đã xóa bài viết.');
    }

    public function categories(?Category $edit = null)
    {
        return view('admin.categories', [
            'categories' => Category::with('parent')->withCount('events')->orderBy('name')->get(),
            'edit' => $edit ?? new Category,
        ]);
    }

    public function saveCategory(Request $request, ?Category $category = null)
    {
        $category ??= new Category;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id', Rule::notIn([$category->id])],
        ]);
        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['name']);
        $category->fill($data)->save();

        return redirect()->route('admin.categories')->with('success', 'Đã lưu dịch vụ.');
    }

    public function deleteCategory(Category $category)
    {
        if ($category->events()->exists() || $category->children()->exists()) {
            return back()->withErrors('Dịch vụ đang có bài viết hoặc danh mục con nên chưa thể xóa.');
        }

        $category->delete();

        return back()->with('success', 'Đã xóa dịch vụ.');
    }

    public function seedServices()
    {
        foreach (['Trang trí bong bóng', 'Ảo thuật', 'Chú hề', 'Kẹo bông gòn', 'Bắp rang bơ', 'Baby Tree', 'Capybara', 'Bong bóng xà phòng', 'Âm nhạc sự kiện'] as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => "Thông tin, hình ảnh và các bài viết giới thiệu dịch vụ {$name}."]
            );
        }

        return back()->with('success', 'Đã bổ sung các dịch vụ mẫu.');
    }

    public function categoryPage(Category $category)
    {
        return view('admin.category-page', [
            'category' => $category,
            'page' => ($category->page ?? $category->page()->make())->loadMissing('contentBlocks'),
        ]);
    }

    public function saveCategoryPage(Request $request, Category $category)
    {
        $page = $category->page ?? $category->page()->make();
        $blocks = $request->input('blocks');

        if (is_array($blocks)) {
            foreach ($blocks as $key => $block) {
                if (is_array($block)) {
                    $blocks[$key]['content'] = PostContent::sanitize($block['content'] ?? null);
                    $blocks[$key]['after_content'] = PostContent::sanitize($block['after_content'] ?? null);
                }
            }

            $request->merge(['blocks' => $blocks]);
        }

        $data = $request->validate([
            'page_title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'category_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'banner_alt' => ['nullable', 'string', 'max:255'],
            'banner_caption' => ['nullable', 'string', 'max:255'],
            'service_image_alt' => ['nullable', 'string', 'max:255'],
            'service_image_caption' => ['nullable', 'string', 'max:255'],
            'service_image_fit' => ['nullable', Rule::in(['cover', 'contain'])],
            'service_image_position_y' => ['nullable', 'integer', 'between:0,100'],
            'banner_image_fit' => ['nullable', Rule::in(['cover', 'contain'])],
            'banner_image_position_y' => ['nullable', 'integer', 'between:0,100'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'service_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'gallery_alts' => ['nullable', 'array'],
            'gallery_alts.*' => ['nullable', 'string', 'max:255'],
            'existing_gallery_alts' => ['nullable', 'array'],
            'existing_gallery_alts.*' => ['nullable', 'string', 'max:255'],
            'feat1_icon' => ['nullable', 'string', 'max:20'],
            'feat1_title' => ['nullable', 'string', 'max:100'],
            'feat1_desc' => ['nullable', 'string', 'max:200'],
            'feat2_icon' => ['nullable', 'string', 'max:20'],
            'feat2_title' => ['nullable', 'string', 'max:100'],
            'feat2_desc' => ['nullable', 'string', 'max:200'],
            'feat3_icon' => ['nullable', 'string', 'max:20'],
            'feat3_title' => ['nullable', 'string', 'max:100'],
            'feat3_desc' => ['nullable', 'string', 'max:200'],
            'cta_text' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'url', 'max:255'],
            'blocks' => ['nullable', 'array', 'max:30'],
            'blocks.*.id' => ['nullable', 'integer'],
            'blocks.*.heading' => ['nullable', 'string', 'max:255'],
            'blocks.*.content' => ['nullable', 'string'],
            'blocks.*.after_content' => ['nullable', 'string'],
            'blocks.*.image_alt' => ['nullable', 'string', 'max:255'],
            'blocks.*.image_caption' => ['nullable', 'string', 'max:255'],
            'blocks.*.image_fit' => ['nullable', Rule::in(['cover', 'contain'])],
            'blocks.*.image_position_y' => ['nullable', 'integer', 'between:0,100'],
            'blocks.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'blocks.*.remove' => ['nullable', 'boolean'],
        ]);

        $blockInputs = $data['blocks'] ?? [];
        unset(
            $data['blocks'],
            $data['gallery_images'],
            $data['gallery_alts'],
            $data['existing_gallery_alts'],
        );

        if ($request->hasFile('banner_image') && blank($data['banner_alt'] ?? null)) {
            throw ValidationException::withMessages(['banner_alt' => 'Ảnh banner bắt buộc phải có Alt ảnh.']);
        }

        foreach ($blockInputs as $key => $blockInput) {
            if ((bool) ($blockInput['remove'] ?? false)) {
                continue;
            }

            $existingImage = filled($blockInput['id'] ?? null)
                ? $page->contentBlocks()->whereKey($blockInput['id'])->value('image')
                : null;

            if (($request->hasFile("blocks.{$key}.image") || $existingImage) && blank($blockInput['image_alt'] ?? null)) {
                throw ValidationException::withMessages([
                    "blocks.{$key}.image_alt" => 'Mỗi ảnh nội dung bắt buộc phải có Alt ảnh.',
                ]);
            }
        }

        foreach (['banner_image' => 'category-banners', 'service_image' => 'category-services'] as $field => $directory) {
            if ($request->hasFile($field)) {
                $this->removeFile($page->{$field});
                $data[$field] = ImageOptimizer::store($request->file($field), $directory, $field === 'service_image' ? 1280 : 1920);
            } else {
                unset($data[$field]);
            }
        }

        if ($request->exists('category_description')) {
            $category->forceFill([
                'description' => filled($data['category_description'] ?? null) ? trim($data['category_description']) : null,
            ])->save();
        }
        unset($data['category_description']);

        $page->fill($data);
        $category->page()->save($page);

        foreach ((array) $request->input('existing_gallery_alts', []) as $imageId => $alt) {
            $page->galleryImages()->whereKey($imageId)->update([
                'alt_text' => filled($alt) ? trim($alt) : null,
            ]);
        }

        $nextGallerySort = (int) $page->galleryImages()->max('sort_order') + 1;

        foreach ((array) $request->file('gallery_images', []) as $index => $file) {
            $page->galleryImages()->create([
                'image_path' => ImageOptimizer::store($file, 'category-gallery'),
                'alt_text' => filled($request->input("gallery_alts.{$index}"))
                    ? trim($request->input("gallery_alts.{$index}"))
                    : null,
                'sort_order' => $nextGallerySort + $index,
            ]);
        }

        foreach ($blockInputs as $key => $blockInput) {
            $block = filled($blockInput['id'] ?? null)
                ? $page->contentBlocks()->findOrFail($blockInput['id'])
                : $page->contentBlocks()->make();

            if ((bool) ($blockInput['remove'] ?? false)) {
                $this->removeFile($block->image);
                $block->delete();

                continue;
            }

            $imageFile = $request->file("blocks.{$key}.image");
            $hasContent = filled($blockInput['heading'] ?? null)
                || filled($blockInput['content'] ?? null)
                || filled($blockInput['after_content'] ?? null)
                || $imageFile
                || $block->image;

            if (! $hasContent) {
                if ($block->exists) {
                    $block->delete();
                }

                continue;
            }

            if ($imageFile) {
                $this->removeFile($block->image);
                $blockInput['image'] = ImageOptimizer::store($imageFile, 'category-content');
            } else {
                unset($blockInput['image']);
            }

            unset($blockInput['id'], $blockInput['remove']);
            $blockInput['heading'] = filled($blockInput['heading'] ?? null) ? trim($blockInput['heading']) : null;
            $blockInput['content'] = filled($blockInput['content'] ?? null) ? trim($blockInput['content']) : null;
            $blockInput['after_content'] = filled($blockInput['after_content'] ?? null) ? trim($blockInput['after_content']) : null;
            $blockInput['image_alt'] = filled($blockInput['image_alt'] ?? null) ? trim($blockInput['image_alt']) : null;
            $blockInput['image_caption'] = filled($blockInput['image_caption'] ?? null) ? trim($blockInput['image_caption']) : null;
            $blockInput['image_fit'] = $blockInput['image_fit'] ?? 'cover';
            $blockInput['image_position_y'] = (int) ($blockInput['image_position_y'] ?? 50);
            $blockInput['sort_order'] = array_search($key, array_keys($blockInputs), true);
            $block->fill($blockInput);
            $page->contentBlocks()->save($block);
        }

        return back()->with('success', 'Đã cập nhật nội dung trang dịch vụ.');
    }

    public function deleteCategoryPageImage(Category $category, string $field)
    {
        abort_unless(in_array($field, ['service_image', 'banner_image'], true), 404);

        $page = $category->page;
        abort_unless($page, 404);

        $this->removeFile($page->{$field});
        $page->forceFill([
            $field => null,
            $field === 'banner_image' ? 'banner_alt' : 'service_image_alt' => null,
            $field === 'banner_image' ? 'banner_caption' : 'service_image_caption' => null,
            $field === 'banner_image' ? 'banner_image_fit' : 'service_image_fit' => 'cover',
            $field === 'banner_image' ? 'banner_image_position_y' : 'service_image_position_y' => 50,
        ])->save();

        $label = $field === 'banner_image' ? 'ảnh banner' : 'ảnh thẻ dịch vụ';

        return redirect()->route('admin.categories.page', $category)->with('success', "Đã xóa {$label}.");
    }

    public function deleteCategoryBlockImage(CategoryContentBlock $block)
    {
        $category = $block->page->category;

        $this->removeFile($block->image);
        $block->forceFill([
            'image' => null,
            'image_alt' => null,
            'image_caption' => null,
            'image_fit' => 'cover',
            'image_position_y' => 50,
        ])->save();

        return redirect()->route('admin.categories.page', $category)->with('success', 'Đã xóa ảnh khỏi khối nội dung.');
    }

    public function deleteCategoryGalleryImage(CategoryPageImage $image)
    {
        $category = $image->page->category;

        $this->removeFile($image->image_path);
        $image->delete();

        return redirect()->route('admin.categories.page', $category)->with('success', 'Đã xóa ảnh khỏi thư viện.');
    }

    public function settings()
    {
        return view('admin.settings', ['settings' => SiteSettings::all()]);
    }

    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'brand_name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'facebook' => ['nullable', 'url', 'max:255'],
            'fanpage' => ['nullable', 'url', 'max:255'],
            'zalo' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:2000'],
            'seo_indexing' => ['required', 'boolean'],
            'robots_allow' => ['nullable', 'string', 'max:2000'],
            'robots_disallow' => ['nullable', 'string', 'max:2000'],
        ]);
        foreach (['robots_allow', 'robots_disallow'] as $field) {
            $data[$field] = collect(preg_split('/\R/', $data[$field] ?? '') ?: [])
                ->map(fn (string $path) => trim($path))
                ->filter(fn (string $path) => Str::startsWith($path, '/'))
                ->unique()
                ->implode("\n");
        }
        SiteSettings::save($data);

        return back()->with('success', 'Đã cập nhật thông tin website.');
    }

    private function removeFile(?string $path): void
    {
        if ($path && Str::contains($path, '/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
