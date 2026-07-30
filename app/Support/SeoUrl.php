<?php

namespace App\Support;

class SeoUrl
{
    public static function route(string $name, mixed $parameters = [], array $query = []): string
    {
        $url = self::base().'/'.ltrim(route($name, $parameters, false), '/');

        return $query === [] ? $url : $url.'?'.http_build_query($query);
    }

    public static function asset(string $path): string
    {
        return self::base().'/'.ltrim($path, '/');
    }

    private static function base(): string
    {
        return rtrim((string) config('app.url'), '/');
    }
}
