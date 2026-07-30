<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\EventImage;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
        ]);
    }

    public function saveEvent(Request $request, ?Event $event = null)
    {
        $event ??= new Event;
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($event)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'event_date' => ['nullable', 'date'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:255'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'extra_images' => ['nullable', 'array', 'max:12'],
            'extra_images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        unset($data['extra_images']);
        $data['slug'] = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['title']);

        if ($request->hasFile('thumbnail')) {
            $this->removeFile($event->thumbnail);
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $event->fill($data)->save();
        $nextSort = (int) $event->images()->max('sort_order') + 1;

        foreach ($request->file('extra_images', []) as $index => $file) {
            $event->images()->create([
                'image_path' => $file->store('events', 'public'),
                'alt_text' => $request->input("alt_texts.{$index}"),
                'sort_order' => $nextSort + $index,
            ]);
        }

        return redirect()->route('admin.events.edit', $event)->with('success', 'Đã lưu bài viết.');
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
            'page' => $category->page ?? $category->page()->make(),
        ]);
    }

    public function saveCategoryPage(Request $request, Category $category)
    {
        $page = $category->page ?? $category->page()->make();
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
        ]);

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
