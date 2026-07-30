<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Support\SeoUrl;
use App\Support\SiteSettings;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $categories = Category::query()
            ->with('page:id,category_id,banner_image,service_image,updated_at')
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at']);

        $events = Event::query()
            ->with('images:id,event_id,image_path')
            ->where('status', 'published')
            ->orderBy('id')
            ->get(['id', 'slug', 'title', 'thumbnail', 'updated_at']);

        $categoryLastModified = $this->latestDate(
            $categories->flatMap(fn (Category $category) => [
                $category->updated_at,
                $category->page?->updated_at,
            ])
        );
        $eventLastModified = $this->latestDate($events->pluck('updated_at'));

        $urls = collect([
            $this->url(SeoUrl::route('home'), $this->latestDate(collect([$categoryLastModified, $eventLastModified]))),
            $this->url(SeoUrl::route('services'), $categoryLastModified),
            $this->url(SeoUrl::route('events'), $eventLastModified),
        ]);

        $urls->push(...$categories->map(fn (Category $category) => $this->url(
            SeoUrl::route('category', $category),
            $this->latestDate(collect([$category->updated_at, $category->page?->updated_at])),
            $this->categoryImages($category)
        )));

        $urls->push(...$events->map(fn (Event $event) => $this->url(
            SeoUrl::route('event', $event),
            $event->updated_at,
            $this->eventImages($event)
        )));

        return response()
            ->view('site.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $settings = SiteSettings::all();
        $indexingEnabled = (bool) ($settings['seo_indexing'] ?? config('seo.indexing_enabled'));
        $lines = ['User-agent: *'];

        if (! $indexingEnabled) {
            $lines[] = 'Disallow: /';
        } else {
            foreach ($this->validRobotPaths($settings['robots_allow'] ?? '') as $path) {
                $lines[] = "Allow: {$path}";
            }

            foreach ($this->validRobotPaths($settings['robots_disallow'] ?? '') as $path) {
                $lines[] = "Disallow: {$path}";
            }

            $lines[] = 'Sitemap: '.SeoUrl::route('sitemap');
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    private function url(string $location, mixed $lastModified, array $images = []): array
    {
        return [
            'location' => $location,
            'last_modified' => $lastModified?->toAtomString(),
            'images' => array_values(array_unique(array_filter($images))),
        ];
    }

    private function categoryImages(Category $category): array
    {
        $page = $category->page;

        return [
            $this->contentImageUrl($page?->banner_image, 'banners'),
            $this->contentImageUrl($page?->service_image, 'services'),
        ];
    }

    private function eventImages(Event $event): array
    {
        $images = $event->images
            ->map(fn ($image) => SeoUrl::asset('storage/'.$image->image_path))
            ->all();

        if ($event->thumbnail) {
            array_unshift($images, Str::startsWith($event->thumbnail, 'thumbnails/')
                ? SeoUrl::asset('storage/'.$event->thumbnail)
                : SeoUrl::asset('uploads/thumbnails/'.$event->thumbnail));
        }

        return $images;
    }

    private function contentImageUrl(?string $path, string $legacyDirectory): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::contains($path, '/')
            ? SeoUrl::asset('storage/'.$path)
            : SeoUrl::asset("uploads/{$legacyDirectory}/{$path}");
    }

    private function latestDate(Collection $dates): mixed
    {
        return $dates->filter()->sortDesc()->first();
    }

    private function validRobotPaths(string $paths): array
    {
        return collect(preg_split('/\R/', $paths) ?: [])
            ->map(fn (string $path) => trim($path))
            ->filter(fn (string $path) => str_starts_with($path, '/'))
            ->unique()
            ->values()
            ->all();
    }
}
