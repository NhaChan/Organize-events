<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use Tests\TestCase;

class EventPricingTest extends TestCase
{
    public function test_admin_can_save_prices_and_public_pages_render_discount_or_contact_price(): void
    {
        $pricedEvent = null;
        $contactEvent = null;
        $slug = 'san-pham-co-gia-'.uniqid();

        try {
            $this->actingAs(Admin::firstOrFail(), 'admin')
                ->get(route('admin.events.create'))
                ->assertOk()
                ->assertSee('name="original_price"', false)
                ->assertSee('name="sale_price"', false)
                ->assertSee('name="content_title"', false)
                ->assertSee('id="add-price-detail"', false);

            $this->post(route('admin.events.save'), [
                'title' => 'Sản phẩm có giá kiểm tra',
                'slug' => $slug,
                'summary' => 'Tóm tắt sản phẩm có giá.',
                'content' => 'Nội dung sản phẩm có giá.',
                'original_price' => 1200000,
                'sale_price' => 900000,
                'content_title' => 'Chi tiết mẫu trang trí',
                'price_details' => [
                    ['label' => 'Bao gồm', 'value' => 'Backdrop và bàn quà'],
                    ['label' => 'Thời gian', 'value' => '3–4 giờ'],
                    ['label' => '', 'value' => ''],
                ],
                'status' => 'published',
            ])->assertSessionHasNoErrors();

            $pricedEvent = Event::where('slug', $slug)->firstOrFail();
            $this->assertSame(1200000, $pricedEvent->original_price);
            $this->assertSame(900000, $pricedEvent->sale_price);
            $this->assertCount(2, $pricedEvent->price_details);
            $pricedEvent->images()->createMany([
                ['image_path' => 'events/goc-chup-1.jpg', 'alt_text' => 'Góc chụp thứ nhất', 'display_fit' => 'contain', 'position_y' => 25, 'sort_order' => 0],
                ['image_path' => 'events/goc-chup-2.jpg', 'alt_text' => 'Góc chụp thứ hai', 'sort_order' => 1],
            ]);

            $this->get(route('events'))
                ->assertOk()
                ->assertSee('<del>1.200.000₫</del>', false)
                ->assertSee('900.000₫')
                ->assertSee('-25%');

            $this->get(route('event', $pricedEvent))
                ->assertOk()
                ->assertSee('data-product-gallery', false)
                ->assertSee('data-product-src', false)
                ->assertSee('data-product-fit="contain"', false)
                ->assertSee('data-product-position-y="25"', false)
                ->assertSee('class="product-price article-main-price"', false)
                ->assertSee('class="product-price-table"', false)
                ->assertSee('Chi tiết mẫu trang trí')
                ->assertSee('Backdrop và bàn quà');

            $contactEvent = Event::create([
                'title' => 'Sản phẩm giá liên hệ kiểm tra',
                'slug' => 'san-pham-gia-lien-he-'.uniqid(),
                'summary' => 'Tóm tắt giá liên hệ.',
                'content' => 'Nội dung giá liên hệ.',
                'status' => 'published',
            ]);

            $this->get(route('events'))
                ->assertOk()
                ->assertSee('Giá liên hệ');

            $this->get(route('events'))
                ->assertOk()
                ->assertSee('class="post-image-more"', false)
                ->assertSee('Xem bài viết →');
        } finally {
            $pricedEvent?->delete();
            $contactEvent?->delete();
        }
    }
}
