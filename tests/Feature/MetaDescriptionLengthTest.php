<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Event;
use Tests\TestCase;

class MetaDescriptionLengthTest extends TestCase
{
    public function test_meta_description_can_exceed_previous_limit(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');
        $slug = 'meta-description-dai-'.uniqid();
        $description = str_repeat('Mô tả SEO dài nhưng vẫn được phép lưu. ', 20);

        $this->post(route('admin.events.save'), [
            'title' => 'Kiểm tra Meta Description dài',
            'slug' => $slug,
            'status' => 'draft',
            'meta_description' => $description,
        ])->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.events.create'));

        $event = Event::where('slug', $slug)->firstOrFail();
        $this->assertSame(trim($description), $event->meta_description);
        $event->delete();
    }

    public function test_meta_description_form_has_warning_but_no_hard_limit(): void
    {
        $this->actingAs(Admin::firstOrFail(), 'admin');

        $this->get(route('admin.events.create'))
            ->assertOk()
            ->assertSee('id="meta-description-count"', false)
            ->assertDontSee('name="meta_description" rows="3" maxlength=', false);
    }
}
