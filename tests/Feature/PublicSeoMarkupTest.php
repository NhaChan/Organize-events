<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Event;
use App\Support\SeoUrl;
use DOMDocument;
use DOMXPath;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PublicSeoMarkupTest extends TestCase
{
    public function test_all_public_page_types_have_valid_seo_markup(): void
    {
        $pages = [
            [route('home'), SeoUrl::route('home')],
            [route('services'), SeoUrl::route('services')],
            [route('events'), SeoUrl::route('events')],
        ];

        foreach (Category::orderBy('id')->get() as $category) {
            $pages[] = [route('category', $category), SeoUrl::route('category', $category)];
        }

        foreach (Event::where('status', 'published')->orderBy('id')->get() as $event) {
            $pages[] = [route('event', $event), SeoUrl::route('event', $event)];
        }

        foreach ($pages as [$url, $canonical]) {
            $this->assertValidSeoMarkup($this->get($url), $canonical);
        }
    }

    private function assertValidSeoMarkup(TestResponse $response, string $expectedCanonical): void
    {
        $response->assertOk();

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML($response->getContent());
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $pageUrl = $expectedCanonical;

        $this->assertSame(1, $xpath->query('//h1')->length, "{$pageUrl} phải có đúng một H1.");
        $this->assertNotSame('', trim($xpath->query('//h1')->item(0)?->textContent ?? ''), "{$pageUrl} có H1 rỗng.");
        $this->assertGreaterThanOrEqual(1, $xpath->query('//h2')->length, "{$pageUrl} cần ít nhất một H2.");

        $headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
        $previousLevel = 0;
        foreach ($headings as $heading) {
            $level = (int) substr($heading->nodeName, 1);
            $this->assertNotSame('', trim($heading->textContent), "{$pageUrl} có heading rỗng.");
            if ($previousLevel > 0) {
                $this->assertLessThanOrEqual($previousLevel + 1, $level, "{$pageUrl} bỏ qua cấp heading.");
            }
            $previousLevel = $level;
        }

        $canonicalNodes = $xpath->query('//head/link[@rel="canonical"]/@href');
        $this->assertSame(1, $canonicalNodes->length, "{$pageUrl} phải có đúng một canonical.");
        $this->assertSame($expectedCanonical, $canonicalNodes->item(0)?->nodeValue, "{$pageUrl} có canonical sai.");
        $this->assertNotSame('', trim($xpath->query('//title')->item(0)?->textContent ?? ''), "{$pageUrl} thiếu title.");
        $this->assertNotSame('', trim($xpath->query('//meta[@name="description"]/@content')->item(0)?->nodeValue ?? ''), "{$pageUrl} thiếu meta description.");

        foreach ($xpath->query('//img') as $image) {
            $this->assertNotSame('', trim($image->getAttribute('src')), "{$pageUrl} có ảnh thiếu src.");
            $this->assertTrue($image->hasAttribute('alt'), "{$pageUrl} có ảnh thiếu thuộc tính alt.");
            $this->assertNotSame('', trim($image->getAttribute('alt')), "{$pageUrl} có ảnh alt rỗng.");
        }

        foreach ($xpath->query('//a') as $link) {
            $href = trim($link->getAttribute('href'));
            $anchorText = trim(preg_replace('/\s+/u', ' ', $link->textContent) ?? '');
            $imageAlt = trim($xpath->query('.//img/@alt', $link)->item(0)?->nodeValue ?? '');

            $this->assertNotSame('', $href, "{$pageUrl} có liên kết thiếu href.");
            $this->assertFalse(str_starts_with(strtolower($href), 'javascript:'), "{$pageUrl} dùng javascript URL.");
            $this->assertTrue($anchorText !== '' || $imageAlt !== '', "{$pageUrl} có liên kết không có nội dung mô tả.");
        }
    }
}
