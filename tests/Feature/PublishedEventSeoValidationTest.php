<?php

namespace Tests\Feature;

use App\Models\Admin;
use Tests\TestCase;

class PublishedEventSeoValidationTest extends TestCase
{
    public function test_published_event_requires_summary_and_content(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin')
            ->post(route('admin.events.save'), [
                'title' => 'Bài viết kiểm tra SEO',
                'status' => 'published',
            ])
            ->assertSessionHasErrors(['summary', 'content']);
    }
}
