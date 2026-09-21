<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryContentBlocksTest extends TestCase
{
    public function test_admin_can_create_optional_category_content_blocks_and_posts_render_first(): void
    {
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Dịch vụ block kiểm tra',
            'slug' => 'dich-vu-block-'.uniqid(),
            'description' => 'Mô tả danh mục kiểm tra.',
        ]);

        try {
            $this->actingAs(Admin::firstOrFail(), 'admin')
                ->post(route('admin.categories.page.save', $category), [
                    'page_title' => 'H1 trang dịch vụ tùy chỉnh',
                    'subtitle' => 'Giới thiệu ngắn dưới H1.',
                    'banner_image' => UploadedFile::fake()->image('anh-banner.jpg', 1600, 700),
                    'banner_alt' => 'Ảnh banner dịch vụ',
                    'banner_caption' => 'Không gian trang trí thực tế',
                    'banner_image_fit' => 'contain',
                    'banner_image_position_y' => 30,
                    'blocks' => [
                        'first' => [
                            'heading' => 'H2 có ảnh minh họa',
                            'content' => 'Đoạn văn thứ nhất có <a href="/bai-viet/noi-bo" onclick="alert(1)">liên kết nội bộ</a>.'."\n\nĐoạn văn thứ hai.",
                            'after_content' => 'Nội dung sau ảnh có <a href="/dich-vu">liên kết dịch vụ</a>.',
                            'image' => UploadedFile::fake()->image('noi-dung.jpg', 1600, 900),
                            'image_alt' => 'Alt bắt buộc của ảnh nội dung',
                            'image_caption' => 'Một góc trang trí tại buổi tiệc',
                            'image_fit' => 'cover',
                            'image_position_y' => 25,
                        ],
                        'second' => [
                            'heading' => 'H2 không cần ảnh',
                            'content' => null,
                        ],
                    ],
                ])
                ->assertSessionHasNoErrors();

            $page = $category->page()->with('contentBlocks')->firstOrFail();
            $this->assertCount(2, $page->contentBlocks);
            $this->assertSame('Không gian trang trí thực tế', $page->banner_caption);
            $this->assertSame('contain', $page->banner_image_fit);
            $this->assertSame(30, $page->banner_image_position_y);
            Storage::disk('public')->assertExists($page->banner_image);
            $this->assertSame('Alt bắt buộc của ảnh nội dung', $page->contentBlocks[0]->image_alt);
            $this->assertSame('Một góc trang trí tại buổi tiệc', $page->contentBlocks[0]->image_caption);
            $this->assertSame(25, $page->contentBlocks[0]->image_position_y);
            $this->assertStringContainsString('<a href="/bai-viet/noi-bo">liên kết nội bộ</a>', $page->contentBlocks[0]->content);
            $this->assertStringNotContainsString('onclick', $page->contentBlocks[0]->content);
            $this->assertStringContainsString('<a href="/dich-vu">liên kết dịch vụ</a>', $page->contentBlocks[0]->after_content);
            Storage::disk('public')->assertExists($page->contentBlocks[0]->image);

            $adminPage = $this->get(route('admin.categories.page', $category));
            $adminPage->assertOk()
                ->assertSee('id="add-content-block"', false)
                ->assertSee('id="page-title-counter"', false)
                ->assertSee('id="category-word-count"', false)
                ->assertSee('name="category_description"', false)
                ->assertSee('id="category-meta-description-count"', false)
                ->assertSee('vẫn có thể lưu')
                ->assertDontSee('name="subtitle"', false)
                ->assertSee('data-word-counter', false)
                ->assertSee('/ 60 ký tự')
                ->assertSee('name="banner_caption"', false)
                ->assertSee('name="banner_image_fit"', false)
                ->assertSee('name="banner_image_position_y"', false)
                ->assertSee('[image_caption]', false)
                ->assertSee('[image_fit]', false)
                ->assertSee('[image_position_y]', false)
                ->assertSee('H2 (tùy chọn)')
                ->assertSee('Alt ảnh *')
                ->assertSee('Chèn liên kết');

            $response = $this->get(route('category', $category))->assertOk();
            $html = $response->getContent();
            $response->assertSee('<h1>H1 trang dịch vụ tùy chỉnh</h1>', false)
                ->assertSee('<p>Mô tả danh mục kiểm tra.</p>', false)
                ->assertDontSee('<p>Giới thiệu ngắn dưới H1.</p>', false)
                ->assertSee('<meta name="description" content="Mô tả danh mục kiểm tra.">', false)
                ->assertSee('class="category-hero"', false)
                ->assertDontSee('data-category-gallery', false)
                ->assertSee('<figcaption class="image-caption">Không gian trang trí thực tế</figcaption>', false)
                ->assertSee('<figcaption class="image-caption">Một góc trang trí tại buổi tiệc</figcaption>', false)
                ->assertSee('style="object-fit:contain;object-position:center 30%"', false)
                ->assertSee('style="object-fit:cover;object-position:center 25%"', false)
                ->assertSee('<div class="category-hero-name">'.$category->name.'</div>', false)
                ->assertDontSee('class="category-visual"', false)
                ->assertSee('<h2>H2 có ảnh minh họa</h2>', false)
                ->assertSee('<a href="/bai-viet/noi-bo">liên kết nội bộ</a>', false)
                ->assertSee('<a href="/dich-vu">liên kết dịch vụ</a>', false)
                ->assertSee('alt="Alt bắt buộc của ảnh nội dung"', false);

            $this->assertLessThan(strpos($html, '<h1>'), strpos($html, 'category-hero-name'));
            $this->assertLessThan(strpos($html, 'category-page-banner'), strpos($html, '<h1>'));
            $this->assertLessThan(strpos($html, 'Bài viết về'), strpos($html, 'category-page-banner'));
            $this->assertLessThan(strpos($html, 'Bài viết về'), strpos($html, '<h1>'));
            $this->assertLessThan(strpos($html, 'category-content-blocks'), strpos($html, 'Bài viết về'));
            $this->assertLessThan(strpos($html, '<a href="/bai-viet/noi-bo">'), strpos($html, '<h2>H2 có ảnh minh họa</h2>'));
            $this->assertLessThan(strpos($html, 'alt="Alt bắt buộc của ảnh nội dung"'), strpos($html, '<a href="/bai-viet/noi-bo">'));
            $this->assertLessThan(strpos($html, '<a href="/dich-vu">'), strpos($html, 'alt="Alt bắt buộc của ảnh nội dung"'));
            $this->assertSame(1, substr_count($html, '<h1'));
        } finally {
            $category->delete();
        }
    }

    public function test_category_meta_description_accepts_long_text_and_is_used_below_h1(): void
    {
        $category = Category::create([
            'name' => 'Kiểm tra giới thiệu dài',
            'slug' => 'kiem-tra-gioi-thieu-dai-'.uniqid(),
        ]);
        $metaDescription = str_repeat('Nội dung giới thiệu giúp người đọc hiểu rõ hơn về dịch vụ. ', 12);

        try {
            $this->actingAs(Admin::firstOrFail(), 'admin')
                ->post(route('admin.categories.page.save', $category), [
                    'category_description' => $metaDescription,
                ])
                ->assertSessionHasNoErrors();

            $this->assertSame(trim($metaDescription), $category->fresh()->description);
            $this->get(route('admin.categories.page', $category))
                ->assertOk()
                ->assertSee('name="category_description"', false)
                ->assertSee('id="category-meta-description-count"', false)
                ->assertSee('vẫn có thể lưu')
                ->assertDontSee('name="category_description" maxlength=', false);

            $this->get(route('category', $category))
                ->assertOk()
                ->assertSee('<p>'.trim($metaDescription).'</p>', false)
                ->assertSee('<meta name="description" content="'.trim($metaDescription).'">', false);
        } finally {
            $category->delete();
        }
    }

    public function test_content_block_image_requires_alt_text(): void
    {
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Kiểm tra Alt block',
            'slug' => 'kiem-tra-alt-block-'.uniqid(),
        ]);

        try {
            $this->actingAs(Admin::firstOrFail(), 'admin')
                ->post(route('admin.categories.page.save', $category), [
                    'blocks' => [
                        'new' => ['image' => UploadedFile::fake()->image('missing-alt.jpg', 1600, 900)],
                    ],
                ])
                ->assertSessionHasErrors(['blocks.new.image_alt']);
        } finally {
            $category->delete();
        }
    }
}
