<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Support\SiteSettings;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    private function base(array $data = []): array
    {
        return array_merge([
            'settings' => SiteSettings::all(),
            'navigationCategories' => Category::query()
                ->whereNull('parent_id')
                ->with(['children' => fn ($query) => $query->orderBy('name')])
                ->orderBy('name')
                ->get(),
        ], $data);
    }

    public function home()
    {
        return view('site.home', $this->base([
            'categories' => Category::whereNull('parent_id')
                ->with('page', 'children')
                ->withCount(['events' => fn ($query) => $query->where('status', 'published')])
                ->orderBy('name')
                ->get(),
            'featured' => Event::with('category')
                ->where('status', 'published')
                ->where(fn ($query) => $query->whereNull('event_date')->orWhere('event_date', '>=', now()))
                ->orderByRaw('event_date IS NULL, event_date')
                ->limit(6)
                ->get(),
            'latest' => Event::with('category')
                ->where('status', 'published')
                ->latest()
                ->limit(8)
                ->get(),
        ]));
    }

    public function events(Request $request)
    {
        $events = Event::with('category')
            ->where('status', 'published')
            ->when($request->string('q')->trim()->toString(), function ($query, $keyword) {
                $query->where(fn ($subQuery) => $subQuery
                    ->where('title', 'like', "%{$keyword}%")
                    ->orWhere('summary', 'like', "%{$keyword}%"));
            })
            ->when($request->string('category')->toString(), function ($query, $slug) {
                $category = Category::where('slug', $slug)->first();

                if ($category) {
                    $query->whereIn('category_id', $this->descendantIds($category));
                }
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('site.events', $this->base([
            'events' => $events,
            'categories' => Category::with('parent')->orderBy('name')->get(),
        ]));
    }

    public function services()
    {
        return view('site.services', $this->base([
            'categories' => Category::whereNull('parent_id')
                ->with('page', 'children')
                ->withCount(['events' => fn ($query) => $query->where('status', 'published')])
                ->orderBy('name')
                ->get(),
        ]));
    }

    public function category(Category $category)
    {
        $category->load('page', 'parent', 'children');
        $categoryIds = $this->descendantIds($category);

        return view('site.category', $this->base([
            'category' => $category,
            'events' => Event::with('category')
                ->whereIn('category_id', $categoryIds)
                ->where('status', 'published')
                ->latest()
                ->paginate(12),
        ]));
    }

    public function event(Event $event)
    {
        abort_unless($event->status === 'published', 404);
        Event::withoutTimestamps(fn () => $event->increment('view_count'));

        return view('site.event', $this->base([
            'event' => $event->load('category.parent', 'images'),
            'related' => Event::with('category')
                ->where('category_id', $event->category_id)
                ->whereKeyNot($event->getKey())
                ->where('status', 'published')
                ->latest()
                ->limit(3)
                ->get(),
        ]));
    }

    private function descendantIds(Category $root): array
    {
        $ids = [$root->id];
        $pending = [$root->id];

        while ($pending !== []) {
            $children = Category::whereIn('parent_id', $pending)->pluck('id')->all();
            $ids = array_merge($ids, $children);
            $pending = $children;
        }

        return array_values(array_unique($ids));
    }
}
