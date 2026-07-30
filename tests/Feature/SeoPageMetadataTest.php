<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Support\SeoUrl;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoPageMetadataTest extends TestCase
{
    public function test_filtered_event_pages_are_noindex_with_a_clean_canonical(): void
    {
        $this->get(route('events', ['q' => 'trang trí']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow,max-image-preview:large">', false)
            ->assertSee('<link rel="canonical" href="'.SeoUrl::route('events').'">', false);
    }

    public function test_paginated_pages_have_self_referencing_canonicals(): void
    {
        $category = Category::firstOrFail();

        $this->get(route('category', $category).'?page=2')
            ->assertOk()
            ->assertSee(
                '<link rel="canonical" href="'.SeoUrl::route('category', $category, ['page' => 2]).'">',
                false
            );
    }

    public function test_event_exposes_blog_posting_schema(): void
    {
        $event = Event::where('status', 'published')->firstOrFail();

        $this->get(route('event', $event))
            ->assertOk()
            ->assertSee('"@type":"BlogPosting"', false)
            ->assertSee('"@id":"'.SeoUrl::route('event', $event).'#article"', false);
    }

    public function test_global_indexing_switch_updates_page_meta(): void
    {
        Storage::fake('local');
        SiteSettings::save(['seo_indexing' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,nofollow,noarchive">', false);
    }

    public function test_view_counter_does_not_change_content_last_modified_date(): void
    {
        $event = Event::where('status', 'published')->firstOrFail();
        $lastModified = $event->updated_at->toAtomString();
        $viewCount = $event->view_count;

        $this->get(route('event', $event))->assertOk();
        $event->refresh();

        $this->assertSame($viewCount + 1, $event->view_count);
        $this->assertSame($lastModified, $event->updated_at->toAtomString());
    }
}
