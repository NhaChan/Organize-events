<?php

namespace App\Support;

class PostContent
{
    /**
     * Keep article copy as plain text plus a deliberately small, safe HTML subset.
     * At the moment the editor only needs anchors and line breaks.
     */
    public static function sanitize(mixed $content): ?string
    {
        if (! is_string($content) || trim($content) === '') {
            return null;
        }

        $content = str_replace(["\0", "\r\n", "\r"], ['', "\n", "\n"], $content);
        $content = preg_replace('/__POST_LINK_[A-F0-9]{16}_\d+__/i', '', $content) ?? $content;
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

        $content = preg_replace('/<br\s*\/?>/iu', "\n", $content) ?? $content;
        $content = preg_replace('/<\/(?:p|div|li|h[1-6])\s*>/iu', "\n", $content) ?? $content;
        $content = preg_replace('/<(?:p|div|ul|ol|li|h[1-6])\b[^>]*>/iu', '', $content) ?? $content;
        $content = self::decode(strip_tags($content));
        $content = str_replace("\u{00A0}", ' ', $content);
        $content = preg_replace("/[ \t]+\n/u", "\n", $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;
        $content = trim($content);

        $html = nl2br(self::escape($content), false);

        return strtr($html, $links) ?: null;
    }

    public static function paragraphs(mixed $content): ?string
    {
        $html = self::sanitize($content);

        if ($html === null) {
            return null;
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
