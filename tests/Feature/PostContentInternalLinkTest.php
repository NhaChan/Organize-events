<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use App\Support\PostContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostContentInternalLinkTest extends TestCase
{
    public function test_content_sanitizer_keeps_safe_links_and_removes_executable_markup(): void
    {
        $content = '<div>Xem <a href="/bai-viet/trang-tri">dịch vụ trang trí</a>.</div>'
            .'<script>alert(1)</script><a href="javascript:alert(2)" onclick="alert(3)">link xấu</a>'
            .'<a href="https://example.com/page" target="_blank" style="color:red">tham khảo</a>';

        $sanitized = PostContent::sanitize($content);

        $this->assertStringContainsString('<a href="/bai-viet/trang-tri">dịch vụ trang trí</a>', $sanitized);
        $this->assertStringContainsString('<a href="https://example.com/page" target="_blank" rel="noopener noreferrer">tham khảo</a>', $sanitized);
        $this->assertStringContainsString('link xấu', $sanitized);
        $this->assertStringNotContainsString('javascript:', $sanitized);
        $this->assertStringNotContainsString('onclick', $sanitized);
        $richContent = PostContent::sanitize("<h2>Tiêu đề giữa bài</h2><p><strong>Chữ đậm</strong> và <font size=\"5\">chữ lớn</font></p><ul><li>Mục một</li></ul>");
        $this->assertSame("<h2>Tiêu đề giữa bài</h2><p><strong>Chữ đậm</strong> và <span class=\"text-size-large\">chữ lớn</span></p><ul><li>Mục một</li></ul>", $richContent);
        $this->assertStringNotContainsString('<script', $sanitized);
    }

    public function test_admin_editor_lists_published_posts_as_internal_link_targets(): void
    {
        $event = Event::where('status', 'published')->firstOrFail();

        $this->actingAs(Admin::firstOrFail(), 'admin')
            ->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('id="content-editor"', false)
            ->assertSee('id="insert-link"', false)
            ->assertSee('id="insert-content-image"', false)
            ->assertSee('id="content-image-dialog"', false)
            ->assertSee('name="content_images[]"', false)
            ->assertSee('data-rich-block="h2"', false)
            ->assertSee('data-rich-block="h3"', false)
            ->assertSee('data-rich-font-size="5"', false)
            ->assertSee('data-rich-command="bold"', false)
            ->assertSee('name="after_gallery_title"', false)
            ->assertSee('name="after_gallery_content"', false)
            ->assertSee('id="new-image-descriptions"', false)
            ->assertSee('name="original_price"', false)
            ->assertSee('name="sale_price"', false)
            ->assertSee(route('event', $event, false), false);
    }

    public function test_admin_can_save_and_public_article_renders_internal_anchor(): void
    {
        $slug = 'kiem-tra-internal-link-'.uniqid();
        $event = null;

        try {
            $this->actingAs(Admin::firstOrFail(), 'admin')
                ->post(route('admin.events.save'), [
                    'title' => 'Kiểm tra internal link',
                    'slug' => $slug,
                    'summary' => 'Nội dung kiểm tra liên kết nội bộ.',
                    'content' => 'Xem <a href="/dich-vu" onclick="alert(1)">các dịch vụ sự kiện</a>.<h2>H2 đặt giữa bài</h2><p><strong>Nội dung nổi bật</strong></p>',
                    'after_gallery_title' => 'Nội dung do admin quản lý',
                    'after_gallery_content' => "Đoạn nội dung đầu tiên.\n\nĐoạn nội dung sau hình ảnh.",
                    'status' => 'published',
                ])
                ->assertSessionHasNoErrors();

            $event = Event::where('slug', $slug)->firstOrFail();
            $this->assertSame('Xem <a href="/dich-vu">các dịch vụ sự kiện</a>.<h2>H2 đặt giữa bài</h2><p><strong>Nội dung nổi bật</strong></p>', $event->content);
            $this->assertSame('Nội dung do admin quản lý', $event->after_gallery_title);
            $this->assertSame("Đoạn nội dung đầu tiên.\n\nĐoạn nội dung sau hình ảnh.", $event->after_gallery_content);

            $image = $event->images()->create([
                'image_path' => 'events/test-description.jpg',
                'sort_order' => 1,
            ]);

            $this->get(route('admin.events.edit', $event))
                ->assertOk()
                ->assertSee('name="existing_image_titles['.$image->id.']"', false)
                ->assertSee('name="existing_image_contents['.$image->id.']"', false)
                ->assertSee('name="existing_alt_texts['.$image->id.']"', false)
                ->assertSee('data-editor-id="image-content-'.$image->id.'"', false);

            $this->post(route('admin.events.save', $event), [
                'title' => $event->title,
                'slug' => $event->slug,
                'summary' => $event->summary,
                'content' => $event->content,
                'after_gallery_title' => $event->after_gallery_title,
                'after_gallery_content' => $event->after_gallery_content,
                'status' => $event->status,
                'existing_image_titles' => [$image->id => '  Tiêu đề riêng của ảnh phụ  '],
                'existing_image_contents' => [$image->id => 'Xem <a href="/dich-vu" onclick="alert(1)">dịch vụ liên quan</a>.'],
                'existing_alt_texts' => [$image->id => '  Alt SEO của ảnh phụ  '],
            ])->assertSessionHasNoErrors();

            $image->refresh();
            $this->assertSame('Tiêu đề riêng của ảnh phụ', $image->title);
            $this->assertSame('Xem <a href="/dich-vu">dịch vụ liên quan</a>.', $image->content);
            $this->assertSame('Alt SEO của ảnh phụ', $image->alt_text);

            $this->get(route('event', $event))
                ->assertOk()
                ->assertSee('<a href="/dich-vu">các dịch vụ sự kiện</a>', false)
                ->assertSee('<h2>H2 đặt giữa bài</h2>', false)
                ->assertSee('<strong>Nội dung nổi bật</strong>', false)
                ->assertSee('class="event-followup"', false)
                ->assertSee('Nội dung do admin quản lý')
                ->assertSee('Đoạn nội dung sau hình ảnh.')
                ->assertSee('<h3>Tiêu đề riêng của ảnh phụ</h3>', false)
                ->assertSee('<a href="/dich-vu">dịch vụ liên quan</a>', false)
                ->assertSee('alt="Alt SEO của ảnh phụ"', false);
            $this->assertStringContainsString(
                'color:#2563eb!important',
                file_get_contents(public_path('css/tailwind-site.css'))
            );
        } finally {
            $event?->delete();
        }
    }
    public function test_admin_can_insert_an_image_between_article_headings_without_adding_it_to_gallery(): void
    {
        Storage::fake("public");
        $slug = "anh-trong-noi-dung-".uniqid();
        $event = null;

        try {
            $this->actingAs(Admin::firstOrFail(), "admin")
                ->post(route("admin.events.save"), [
                    "title" => "Kiểm tra ảnh trong nội dung",
                    "slug" => $slug,
                    "summary" => "Kiểm tra ảnh chèn giữa các tiêu đề.",
                    "content" => "<h2>Phần đầu</h2><figure data-content-image-token=\"0\"><img src=\"blob:test\" data-content-image-token=\"0\"><figcaption>Chú thích ảnh ở giữa bài</figcaption></figure><h3>Phần tiếp theo</h3>",
                    "content_images" => [UploadedFile::fake()->image("anh-giua-bai.jpg", 1200, 800)],
                    "content_image_alts" => ["Không gian trang trí trong bài viết"],
                    "status" => "published",
                ])
                ->assertSessionHasNoErrors();

            $event = Event::where("slug", $slug)->firstOrFail();
            $this->assertSame(0, $event->images()->count());
            $this->assertStringContainsString("<h2>Phần đầu</h2>", $event->content);
            $this->assertStringContainsString("<h3>Phần tiếp theo</h3>", $event->content);
            $this->assertStringContainsString("alt=\"Không gian trang trí trong bài viết\"", $event->content);
            $this->assertStringContainsString("<figcaption>Chú thích ảnh ở giữa bài</figcaption>", $event->content);
            preg_match("#/storage/(content-images/[^\"]+)#", $event->content, $matches);
            $this->assertNotEmpty($matches[1] ?? null);
            Storage::disk("public")->assertExists($matches[1]);

            $this->get(route("event", $event))
                ->assertOk()
                ->assertSee("<figure>", false)
                ->assertSee("<figcaption>Chú thích ảnh ở giữa bài</figcaption>", false);

            $this->delete(route("admin.events.delete", $event))->assertSessionHasNoErrors();
            Storage::disk("public")->assertMissing($matches[1]);
            $event = null;
        } finally {
            if ($event) Event::whereKey($event->getKey())->delete();
        }
    }

}
