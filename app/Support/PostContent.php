<?php

namespace App\Support;

class PostContent
{
    /**
     * Keep article copy as plain text plus a deliberately small, safe HTML subset.
     * Rich formatting is restricted to headings, emphasis, lists, quotes, safe links and preset text sizes.
     */
    public static function sanitize(mixed $content): ?string
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $content = str_replace(["\0", "\r\n", "\r"], ['', "\n", "\n"], $content);
        $content = preg_replace('/__(?:POST_LINK|POST_TAG|POST_IMAGE)_[A-F0-9]{16}_\d+__/i', '', $content) ?? $content;
        $content = preg_replace('/<(script|style|iframe|object|embed|svg|math)\b[^>]*>.*?<\/\1\s*>/isu', '', $content) ?? $content;

        $links = [];
        $tokenPrefix = '__POST_LINK_'.strtoupper(substr(hash('sha256', $content), 0, 16)).'_';
        $content = preg_replace_callback('/<a\b([^>]*)>(.*?)<\/a\s*>/isu', function (array $match) use (&$links, $tokenPrefix) {
            $label = self::plainText($match[2]);
            $href = self::attribute($match[1], 'href');
            $safeUrl = self::safeUrl($href);
            $token = $tokenPrefix.count($links).'__';

            if ($safeUrl === null || $label === '') {
                $links[$token] = self::escape($label);

                return $token;
            }

            $newTab = self::attribute($match[1], 'target') === '_blank';
            $attributes = ' href="'.self::escape($safeUrl).'"';
            if ($newTab) {
                $attributes .= ' target="_blank" rel="noopener noreferrer"';
            }

            $links[$token] = '<a'.$attributes.'>'.self::escape($label).'</a>';

            return $token;
        }, $content) ?? $content;

        $images = [];
        $imageTokenPrefix = "__POST_IMAGE_".strtoupper(substr(hash("sha256", "images|".$content), 0, 16))."_";
        $content = preg_replace_callback("/<img\b([^>]*)>/iu", function (array $match) use (&$images, $imageTokenPrefix) {
            $src = self::attribute($match[1], "src");
            if (! is_string($src) || ! preg_match("#^/storage/content-images/[a-zA-Z0-9/_\.-]+$#", $src)) {
                return "";
            }

            $alt = trim(self::attribute($match[1], "alt") ?? "");
            $token = $imageTokenPrefix.count($images)."__";
            $images[$token] = "<img src=\"".self::escape($src)."\" alt=\"".self::escape($alt)."\" loading=\"lazy\" decoding=\"async\">";

            return $token;
        }, $content) ?? $content;

        $safeTags = [];
        $tagTokenPrefix = "__POST_TAG_".strtoupper(substr(hash("sha256", "tags|".$content), 0, 16))."_";
        $content = preg_replace_callback("/<\/?(?:p|div|h2|h3|blockquote|ul|ol|li|strong|b|em|i|u|font|span|figure|figcaption)\b[^>]*>/iu", function (array $match) use (&$safeTags, $tagTokenPrefix) {
            $isClosing = str_starts_with($match[0], "</");
            preg_match("/<\/?\s*([a-z0-9]+)/i", $match[0], $nameMatch);
            $tag = strtolower($nameMatch[1] ?? "");
            $tag = ["b" => "strong", "i" => "em"][$tag] ?? $tag;
            $safeTag = $isClosing ? "</".($tag === "font" ? "span" : $tag).">" : "<".$tag.">";

            if (! $isClosing && $tag === "span") {
                $class = self::attribute($match[0], "class");
                $allowedClasses = ["text-size-small", "text-size-normal", "text-size-medium", "text-size-large"];
                $safeTag = in_array($class, $allowedClasses, true) ? "<span class=\"".$class."\">" : "<span>";
            }

            if (! $isClosing && $tag === "font") {
                $size = (int) (self::attribute($match[0], "size") ?? 3);
                $class = match (true) {
                    $size <= 2 => "text-size-small",
                    $size >= 5 => "text-size-large",
                    $size === 4 => "text-size-medium",
                    default => "text-size-normal",
                };
                $safeTag = "<span class=\"".$class."\">";
            }

            $token = $tagTokenPrefix.count($safeTags)."__";
            $safeTags[$token] = $safeTag;

            return $token;
        }, $content) ?? $content;

        $content = preg_replace('/<br\s*\/?>/iu', "\n", $content) ?? $content;
        $content = preg_replace('/<\/(?:p|div|li|h[1-6])\s*>/iu', "\n", $content) ?? $content;
        $content = preg_replace('/<(?:p|div|ul|ol|li|h[1-6])\b[^>]*>/iu', '', $content) ?? $content;
        $content = self::decode(strip_tags($content));
        $content = str_replace("\u{00A0}", ' ', $content);
        $content = preg_replace("/[ \t]+\n/u", "\n", $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;
        $content = trim($content);

        $html = nl2br(self::escape($content), false);

        return strtr($html, $links + $images + $safeTags) ?: null;
    }

    public static function paragraphs(mixed $content): ?string
    {
        $html = self::sanitize($content);

        if ($html === null) {
            return null;
        }
        if (preg_match("/<(?:p|div|h2|h3|blockquote|ul|ol|figure)\b/i", $html)) {
            return $html;
        }

        $parts = preg_split('/(?:<br>\s*){2,}/i', $html) ?: [$html];
        $parts = array_values(array_filter(
            array_map('trim', $parts),
            static fn (string $part): bool => $part !== ''
        ));

        return $parts === []
            ? null
            : implode('', array_map(static fn (string $part): string => '<p>'.$part.'</p>', $parts));
    }

    private static function attribute(string $attributes, string $name): ?string
    {
        $pattern = '/\b'.preg_quote($name, '/').'\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/iu';
        if (! preg_match($pattern, $attributes, $matches)) {
            return null;
        }

        foreach (array_slice($matches, 1) as $value) {
            if ($value !== '') {
                return self::decode($value);
            }
        }

        return '';
    }

    private static function safeUrl(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $url) ?? '');
        if ($url === '' || str_contains($url, '\\') || str_starts_with($url, '//')) {
            return null;
        }

        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? $url : null;
    }

    private static function plainText(string $html): string
    {
        $html = preg_replace('/<br\s*\/?>/iu', "\n", $html) ?? $html;

        return trim(self::decode(strip_tags($html)));
    }

    private static function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
