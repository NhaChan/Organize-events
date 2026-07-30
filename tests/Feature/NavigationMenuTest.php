<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use Tests\TestCase;

class NavigationMenuTest extends TestCase
{
    public function test_public_header_renders_service_category_tree(): void
    {
        $parent = Category::whereNull('parent_id')->whereHas('children')->with('children')->firstOrFail();
        $child = $parent->children->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('id="service-mega-menu"', false)
            ->assertSee('data-service-toggle', false)
            ->assertSee(route('category', $parent), false)
            ->assertSee($parent->name)
            ->assertSee(route('category', $child), false)
            ->assertSee($child->name);
    }

    public function test_service_menu_is_available_on_inner_pages(): void
    {
        $event = Event::where('status', 'published')->firstOrFail();

        $this->get(route('event', $event))
            ->assertOk()
            ->assertSee('id="service-mega-menu"', false)
            ->assertSee('aria-controls="main-navigation"', false);
    }
}
