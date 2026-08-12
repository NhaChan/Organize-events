<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventImage;
use App\Support\PostContent;
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
        $request->merge([
            'content' => PostContent::sanitize($request->input('content')),
            'image_contents' => $this->sanitizeContentList($request->input('image_contents')),
            'existing_image_contents' => $this->sanitizeContentList($request->input('existing_image_contents')),
        ]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($event)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'summary' => ['required_if:status,published', 'nullable', 'string', 'max:1000'],
            'content' => ['required_if:status,published', 'nullable', 'string'],
            'after_gallery_title' => ['nullable', 'string', 'max:255'],
            'after_gallery_content' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'extra_images' => ['nullable', 'array', 'max:12'],
            'extra_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'alt_texts' => ['nullable', 'array'],
            'alt_texts.*' => ['nullable', 'string', 'max:255'],
            'image_titles' => ['nullable', 'array'],
            'image_titles.*' => ['nullable', 'string', 'max:255'],
            'image_contents' => ['nullable', 'array'],
            'image_contents.*' => ['nullable', 'string'],
            'existing_alt_texts' => ['nullable', 'array'],
            'existing_alt_texts.*' => ['nullable', 'string', 'max:255'],
            'existing_image_titles' => ['nullable', 'array'],
            'existing_image_titles.*' => ['nullable', 'string', 'max:255'],
            'existing_image_contents' => ['nullable', 'array'],
            'existing_image_contents.*' => ['nullable', 'string'],
        ]);

        unset($data['extra_images'], $data['alt_texts'], $data['image_titles'], $data['image_contents'], $data['existing_alt_texts'], $data['existing_image_titles'], $data['existing_image_contents']);
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);

        if ($request->hasFile('thumbnail')) {
            $this->removeFile($event->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $event->fill($data)->save();

        $existingImageIds = collect([
            array_keys($request->input('existing_alt_texts', [])),
            array_keys($request->input('existing_image_titles', [])),
            array_keys($request->input('existing_image_contents', [])),
        ])->flatten()->unique();

        foreach ($existingImageIds as $imageId) {
            $title = $request->input("existing_image_titles.{$imageId}");
            $content = $request->input("existing_image_contents.{$imageId}");
            $alt = $request->input("existing_alt_texts.{$imageId}");
            $event->images()->whereKey($imageId)->update([
                'title' => filled($title) ? trim($title) : null,
                'content' => filled($content) ? $content : null,
                'alt_text' => filled($alt) ? trim($alt) : null,
            ]);
        }

        $nextSort = (int) $event->images()->max('sort_order') + 1;

        foreach ($request->file('extra_images', []) as $index => $file) {
            $event->images()->create([
                'image_path' => $file->store('events', 'public'),
                'title' => filled($request->input("image_titles.{$index}"))
                    ? trim($request->input("image_titles.{$index}"))
                    : null,
                'content' => $request->input("image_contents.{$index}"),
                'alt_text' => filled($request->input("alt_texts.{$index}"))
                    ? trim($request->input("alt_texts.{$index}"))
                    : null,
                'sort_order' => $nextSort + $index,
            ]);
        }

        return redirect()->route('admin.events.edit', $event)->with('success', 'Đã lưu bài viết.');
    }

    private function sanitizeContentList(mixed $contents): mixed
    {
        if (! is_array($contents)) {
            return $contents;
        }

        return array_map(fn ($content) => PostContent::sanitize($content), $contents);
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
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);
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
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'banner_alt' => ['nullable', 'string', 'max:255'],
            'service_image_alt' => ['nullable', 'string', 'max:255'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'service_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
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
            'blocks.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:6144'],
            'blocks.*.remove' => ['nullable', 'boolean'],
        ]);

        $blockInputs = $data['blocks'] ?? [];
        unset($data['blocks']);

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
                $data[$field] = $request->file($field)->store($directory, 'public');
            } else {
                unset($data[$field]);
            }
        }

        $page->fill($data);
        $category->page()->save($page);

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
                $blockInput['image'] = $imageFile->store('category-content', 'public');
            } else {
                unset($blockInput['image']);
            }

            unset($blockInput['id'], $blockInput['remove']);
            $blockInput['heading'] = filled($blockInput['heading'] ?? null) ? trim($blockInput['heading']) : null;
            $blockInput['content'] = filled($blockInput['content'] ?? null) ? trim($blockInput['content']) : null;
            $blockInput['after_content'] = filled($blockInput['after_content'] ?? null) ? trim($blockInput['after_content']) : null;
            $blockInput['image_alt'] = filled($blockInput['image_alt'] ?? null) ? trim($blockInput['image_alt']) : null;
            $blockInput['sort_order'] = array_search($key, array_keys($blockInputs), true);
            $block->fill($blockInput);
            $page->contentBlocks()->save($block);
        }

        return back()->with('success', 'Đã cập nhật nội dung trang dịch vụ.');
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
