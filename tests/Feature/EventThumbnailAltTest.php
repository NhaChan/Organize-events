<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventThumbnailAltTest extends TestCase
{
    public function test_admin_form_exposes_image_fit_and_vertical_position_controls(): void
    {
        $event = Event::firstOrFail();

        $this->actingAs(Admin::firstOrFail(), 'admin')
            ->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('name="thumbnail_fit"', false)
            ->assertSee('name="thumbnail_position_y"', false)
            ->assertSee('data-image-position', false)
            ->assertSee('Vừa khung, không cắt');
    }

    public function test_form_requires_reselecting_an_image_lost_after_validation_redirect(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');

        $this->post(route('admin.events.save'), [
            'title' => 'Bài viết cần chọn lại ảnh',
            'slug' => 'bai-viet-can-chon-lai-anh-'.uniqid(),
            'status' => 'draft',
            'thumbnail_alt' => 'Alt ảnh đã chọn trước đó',
            'had_thumbnail_upload' => '1',
        ])->assertSessionHasErrors('thumbnail');
    }

    public function test_empty_image_fields_do_not_cause_server_error(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');
        $slug = 'kiem-tra-anh-rong-'.uniqid();

        $this->post(route('admin.events.save'), [
            'title' => 'Bài viết kiểm tra dữ liệu ảnh rỗng',
            'slug' => $slug,
            'status' => 'draft',
            'existing_alt_texts' => null,
            'existing_image_titles' => null,
            'existing_image_contents' => null,
            'extra_images' => null,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.create'))
            ->assertSessionHas('success');

        Event::where('slug', $slug)->delete();
    }

    public function test_normalized_duplicate_slug_returns_validation_error_instead_of_server_error(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');
        $existing = Event::firstOrFail();

        $this->post(route('admin.events.save'), [
            'title' => 'Bài viết bị trùng slug',
            'slug' => str_replace('-', ' ', $existing->slug),
            'status' => 'draft',
        ])->assertSessionHasErrors('slug');
    }

    public function test_thumbnail_requires_alt_and_saves_it(): void
    {
        Storage::fake('public');
        $this->actingAs(Admin::firstOrFail(), 'admin');

        $base = [
            'title' => 'Bài viết kiểm tra Alt ảnh chính',
            'slug' => 'kiem-tra-alt-anh-chinh-'.uniqid(),
            'summary' => 'Tóm tắt bài viết kiểm tra Alt ảnh chính.',
            'content' => 'Nội dung bài viết kiểm tra Alt ảnh chính.',
            'status' => 'published',
        ];

        $this->post(route('admin.events.save'), $base + [
            'thumbnail' => UploadedFile::fake()->image('anh-chinh.jpg'),
        ])->assertSessionHasErrors('thumbnail_alt');

        $this->post(route('admin.events.save'), $base + [
            'thumbnail' => UploadedFile::fake()->image('anh-chinh.jpg'),
            'thumbnail_alt' => 'Không gian sinh nhật trang trí màu hồng',
        ])->assertSessionHasNoErrors();

        $event = Event::where('slug', $base['slug'])->firstOrFail();
        $this->assertSame('Không gian sinh nhật trang trí màu hồng', $event->thumbnail_alt);
        $event->delete();
    }

    public function test_admin_can_upload_more_than_twelve_extra_images(): void
    {
        Storage::fake('public');
        $this->actingAs(Admin::firstOrFail(), 'admin');
        $slug = 'nhieu-anh-phu-'.uniqid();
        $images = collect(range(1, 13))
            ->map(fn (int $index) => UploadedFile::fake()->image("anh-phu-{$index}.jpg"))
            ->all();

        $this->post(route('admin.events.save'), [
            'title' => 'Bài viết có nhiều ảnh phụ',
            'slug' => $slug,
            'status' => 'draft',
            'had_extra_images_upload' => '1',
            'extra_images' => $images,
            'image_fits' => [0 => 'contain'],
            'image_positions' => [0 => 20],
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.create'));

        $event = Event::where('slug', $slug)->firstOrFail();
        $this->assertCount(13, $event->images);
        $this->assertSame('contain', $event->images->first()->display_fit);
        $this->assertSame(20, $event->images->first()->position_y);
        $event->images()->delete();
        $event->delete();
    }
}
