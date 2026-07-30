<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoEndpointsTest extends TestCase
{
    public function test_sitemap_contains_public_content_and_valid_metadata(): void
    {
        $published = Event::where('status', 'published')->firstOrFail();
        $draft = Event::where('status', 'draft')->first();

        $response = $this->get(route('sitemap'));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('event', $published), false)
            ->assertSee('<lastmod>', false);

        if ($draft) {
            $response->assertDontSee(route('event', $draft), false);
        }
    }

    public function test_robots_can_disable_indexing(): void
    {
        Storage::fake('local');
        SiteSettings::save(['seo_indexing' => false]);

        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nDisallow: /", false)
            ->assertDontSee('Sitemap:', false);
    }

    public function test_robots_advertises_sitemap_when_indexing_is_enabled(): void
    {
        Storage::fake('local');
        SiteSettings::save(['seo_indexing' => true]);

        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Allow: /*.js$', false)
            ->assertSee('Disallow: /admin/', false)
            ->assertSee('Disallow: /*?q=', false)
            ->assertSee('Disallow: /login', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }
}
