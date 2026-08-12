<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Event;
use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EventBlogTest extends TestCase
{
    public function test_public_pages_render_successfully(): void
    {
        $this->get('/')->assertOk()->assertSee('Minh Triều Party')->assertDontSee('class="service-sidebar"', false);
        $this->get('/')->assertSee('class="float-zalo"', false)->assertSee('https://zalo.me/', false);
        $this->get('/dich-vu')->assertOk()->assertSee('DỊCH VỤ SỰ KIỆN')->assertSee('class="service-sidebar"', false);
        $this->get('/dich-vu')->assertDontSee('Tất cả dịch vụ');
        $this->get('/bai-viet')->assertOk()->assertSee('class="service-sidebar"', false);
        $this->get('/sitemap.xml')->assertOk();

        $category = Category::firstOrFail();
        $this->get(route('category', $category))->assertOk()->assertSee($category->name)->assertSee('class="service-sidebar"', false);
        $this->get('/')->assertSee('class="nav-category"', false)->assertDontSee('>Dịch vụ</a>', false);

        $event = Event::where('status', 'published')->firstOrFail();
        $this->get(route('event', $event))->assertOk()->assertSee($event->title)->assertSee('class="service-sidebar"', false);
    }

    public function test_admin_pages_require_login_and_render_after_authentication(): void
    {
        $this->get('/admin')->assertRedirect(route('login'));
        $this->get('/admin/login')->assertOk()->assertSee('Đăng nhập');

        $this->actingAs(Admin::firstOrFail(), 'admin');
        $this->get('/admin')->assertOk();
        $this->get('/admin/events')->assertOk();
        $this->get('/admin/events/create')->assertOk();
        $this->get('/admin/categories')->assertOk();
        $this->get(route('admin.categories.page', Category::firstOrFail()))->assertOk();
        $this->get('/admin/settings/site')->assertOk()->assertSee('confirm-modal');
        $this->get('/admin/settings/site')->assertSee('name="zalo"', false);
    }

    public function test_admin_can_create_a_category(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');

        $name = 'Dịch vụ kiểm thử '.uniqid();
        $response = $this->post(route('admin.categories.save'), [
            'name' => $name,
            'description' => 'Danh mục được tạo bởi kiểm thử chức năng admin.',
        ]);

        $response->assertRedirect(route('admin.categories'));
        $category = Category::where('name', $name)->firstOrFail();
        $category->delete();
    }

    public function test_missing_urls_and_slugs_redirect_to_home_instead_of_showing_404(): void
    {
        $this->get('/duong-dan-khong-ton-tai-'.uniqid())
            ->assertRedirect(route('home'));

        $this->get('/dich-vu/danh-muc-da-bi-xoa-'.uniqid())
            ->assertRedirect(route('home'));

        $this->get('/bai-viet/bai-viet-da-bi-xoa-'.uniqid())
            ->assertRedirect(route('home'));
    }

    public function test_admin_can_login_with_seeded_credentials(): void
    {
        $this->post('/admin/login', [
            'username' => env('ADMIN_SEED_USERNAME'),
            'password' => env('ADMIN_SEED_PASSWORD'),
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated('admin');
    }

    public function test_admin_can_request_a_password_reset_email(): void
    {
        Notification::fake();
        $admin = Admin::firstOrFail();
        DB::table('password_reset_tokens')->where('email', $admin->email)->delete();

        $this->get(route('admin.password.request'))->assertOk();
        $this->post(route('admin.password.email'), ['email' => $admin->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($admin, AdminResetPasswordNotification::class);
        DB::table('password_reset_tokens')->where('email', $admin->email)->delete();
    }
}
