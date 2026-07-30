<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Tests\TestCase;

class PublicEventFilterTest extends TestCase
{
    public function test_event_page_renders_the_new_filter_controls(): void
    {
        $this->get(route('events'))
            ->assertOk()
            ->assertSee('class="event-filter-card"', false)
            ->assertSee('Lọc nội dung')
            ->assertSee('bài viết phù hợp')
            ->assertDontSee('class="event-filter-reset"', false);
    }

    public function test_active_filters_render_reset_action_and_selected_values(): void
    {
        $category = Category::firstOrFail();

        $this->get(route('events', ['q' => 'sinh nhật', 'category' => $category->slug]))
            ->assertOk()
            ->assertSee('class="event-filter-reset"', false)
            ->assertSee('Đặt lại')
            ->assertSee('“sinh nhật”')
            ->assertSee($category->name);
    }

    public function test_parent_category_filter_includes_child_category_posts(): void
    {
        $parent = Category::whereNull('parent_id')
            ->whereHas('children.events', fn ($query) => $query->where('status', 'published'))
            ->with('children')
            ->firstOrFail();
        $event = Event::where('status', 'published')
            ->whereIn('category_id', $parent->children->pluck('id'))
            ->firstOrFail();

        $this->get(route('events', ['category' => $parent->slug]))
            ->assertOk()
            ->assertSee($event->title);
    }
}
