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
                ->assertSee('name="sale_price"', false);

            $this->post(route('admin.events.save'), [
                'title' => 'Sản phẩm có giá kiểm tra',
                'slug' => $slug,
                'summary' => 'Tóm tắt sản phẩm có giá.',
                'content' => 'Nội dung sản phẩm có giá.',
                'original_price' => 1200000,
                'sale_price' => 900000,
                'status' => 'published',
            ])->assertSessionHasNoErrors();

            $pricedEvent = Event::where('slug', $slug)->firstOrFail();
            $this->assertSame(1200000, $pricedEvent->original_price);
            $this->assertSame(900000, $pricedEvent->sale_price);

            $this->get(route('events'))
                ->assertOk()
                ->assertSee('<del>1.200.000₫</del>', false)
                ->assertSee('900.000₫')
                ->assertSee('-25%');

            $this->get(route('event', $pricedEvent))
                ->assertOk()
                ->assertDontSee('class="product-price"', false);

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
