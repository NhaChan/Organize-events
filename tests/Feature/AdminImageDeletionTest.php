<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminImageDeletionTest extends TestCase
{
    public function test_admin_can_delete_uploaded_images_without_deleting_content(): void
    {
        Storage::fake('public');
        $this->actingAs(Admin::firstOrFail(), 'admin');

        Storage::disk('public')->put('thumbnails/delete-thumbnail.jpg', 'thumbnail');
        $event = Event::create([
            'title' => 'Kiểm tra xóa ảnh '.uniqid(),
            'slug' => 'kiem-tra-xoa-anh-'.uniqid(),
            'status' => 'draft',
            'thumbnail' => 'thumbnails/delete-thumbnail.jpg',
            'thumbnail_alt' => 'Ảnh cần xóa',
        ]);

        $this->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('data-clear-selected-thumbnail', false)
            ->assertSee('Xóa ảnh đã chọn')
            ->assertSee('delete-event-thumbnail', false);

        $this->delete(route('admin.events.thumbnail.delete', $event))
            ->assertRedirect(route('admin.events.edit', $event))
            ->assertSessionHas('success');

        $event->refresh();
        $this->assertNull($event->thumbnail);
        $this->assertNull($event->thumbnail_alt);
        Storage::disk('public')->assertMissing('thumbnails/delete-thumbnail.jpg');

        $category = Category::create([
            'name' => 'Danh mục xóa ảnh '.uniqid(),
            'slug' => 'danh-muc-xoa-anh-'.uniqid(),
        ]);
        Storage::disk('public')->put('category-services/delete-service.jpg', 'service');
        Storage::disk('public')->put('category-banners/delete-banner.jpg', 'banner');
        $page = $category->page()->create([
            'service_image' => 'category-services/delete-service.jpg',
            'service_image_alt' => 'Ảnh thẻ',
            'banner_image' => 'category-banners/delete-banner.jpg',
            'banner_alt' => 'Ảnh banner',
        ]);
        Storage::disk('public')->put('category-content/delete-block.jpg', 'block');
        $block = $page->contentBlocks()->create([
            'heading' => 'Nội dung phải được giữ lại',
            'image' => 'category-content/delete-block.jpg',
            'image_alt' => 'Ảnh nội dung',
            'sort_order' => 0,
        ]);

        $this->get(route('admin.categories.page', $category))
            ->assertOk()
            ->assertSee('delete-service-image', false)
            ->assertSee('delete-banner-image', false)
            ->assertSee('delete-block-image-'.$block->id, false);

        foreach (['service_image', 'banner_image'] as $field) {
            $this->delete(route('admin.categories.page.images.delete', [$category, $field]))
                ->assertRedirect(route('admin.categories.page', $category));
        }

        $this->delete(route('admin.category-content-blocks.image.delete', $block))
            ->assertRedirect(route('admin.categories.page', $category));

        $page->refresh();
        $block->refresh();
        $this->assertNull($page->service_image);
        $this->assertNull($page->banner_image);
        $this->assertNull($block->image);
        $this->assertSame('Nội dung phải được giữ lại', $block->heading);
        Storage::disk('public')->assertMissing('category-services/delete-service.jpg');
        Storage::disk('public')->assertMissing('category-banners/delete-banner.jpg');
        Storage::disk('public')->assertMissing('category-content/delete-block.jpg');

        $event->delete();
        $block->delete();
        $page->delete();
        $category->delete();
    }
}
