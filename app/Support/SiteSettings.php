<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class SiteSettings
{
    public static function defaults(): array
    {
        return [
            'brand_name' => 'Minh Triệu Party',
            'tagline' => 'Biến ngày vui thành kỷ niệm đáng nhớ',
            'phone' => '0909 000 000',
            'facebook' => 'https://www.facebook.com/minh.trieu.715595',
            'fanpage' => 'https://www.facebook.com/minh.trieu.715595',
            'address' => 'TP. Hồ Chí Minh',
            'about' => 'Chuyên trang trí và cung cấp dịch vụ giải trí cho sinh nhật, thôi nôi, khai trương, trường học và sự kiện gia đình.',
            'seo_indexing' => (bool) config('seo.indexing_enabled'),
            'robots_allow' => implode("\n", config('seo.robots.allow', [])),
            'robots_disallow' => implode("\n", config('seo.robots.disallow', [])),
        ];
    }

    public static function all(): array
    {
        $data = Storage::disk('local')->exists('site-settings.json') ? json_decode(Storage::disk('local')->get('site-settings.json'), true) : [];

        return array_merge(self::defaults(), is_array($data) ? $data : []);
    }

    public static function save(array $data): void
    {
        Storage::disk('local')->put('site-settings.json', json_encode(array_merge(self::defaults(), $data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
