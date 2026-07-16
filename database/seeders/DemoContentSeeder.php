<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'Trang trí bong bóng' => 'Thiết kế không gian tiệc theo chủ đề và màu sắc riêng.',
            'Ảo thuật' => 'Tiết mục tương tác vui nhộn dành cho trẻ em và gia đình.',
            'Chú hề' => 'Hoạt náo, tạo hình bong bóng và dẫn dắt trò chơi.',
            'Kẹo bông gòn' => 'Quầy kẹo bông trực tiếp, sạch đẹp và hấp dẫn.',
            'Bắp rang bơ' => 'Quầy bắp rang thơm nóng cho ngày hội và sinh nhật.',
            'Âm nhạc sự kiện' => 'Âm thanh và chương trình âm nhạc phù hợp không gian tiệc.',
        ];

        foreach ($services as $name => $description) {
            Category::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'description' => $description]);
        }

        $category = Category::where('slug', 'trang-tri-bong-bong')->first();
        Event::firstOrCreate(['slug' => 'khong-gian-sinh-nhat-hong-cam'], [
            'category_id' => $category?->id,
            'title' => 'Không gian sinh nhật tone hồng cam rực rỡ',
            'summary' => 'Một gợi ý trang trí ấm áp, trẻ trung với bong bóng hồng, cam và ánh vàng.',
            'content' => "Tone hồng cam mang lại cảm giác vui tươi nhưng vẫn ấm áp. Bố cục có thể kết hợp cổng bong bóng, bàn bánh và khu vực chụp ảnh để tạo thành một không gian thống nhất.\n\nLiên hệ trực tiếp để được tư vấn chủ đề, kích thước và ngân sách phù hợp.",
            'event_date' => now()->addMonth(),
            'location' => 'TP. Hồ Chí Minh',
            'status' => 'published',
            'meta_title' => 'Trang trí sinh nhật tone hồng cam',
            'meta_description' => 'Gợi ý không gian sinh nhật hồng cam đẹp, trẻ trung và ấm áp.',
        ]);
    }
}
