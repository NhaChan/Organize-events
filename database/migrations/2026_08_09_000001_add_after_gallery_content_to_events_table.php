<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('after_gallery_title')->nullable()->after('content');
            $table->longText('after_gallery_content')->nullable()->after('after_gallery_title');
        });

        $defaultContent = "Để chương trình diễn ra chỉn chu, các hạng mục nên được thống nhất sớm theo diện tích thực tế, số lượng khách và độ tuổi người tham dự. Việc xác định rõ chủ đề, tông màu và những khu vực sử dụng chính sẽ giúp tổng thể hài hòa, đồng thời hạn chế phát sinh khi thi công.\n\nVới mỗi không gian, kích thước và cách bố trí cần được điều chỉnh thay vì áp dụng một mẫu cố định. Nên ưu tiên điểm nhìn chính, lối di chuyển và khu vực chụp ảnh để vừa bảo đảm thẩm mỹ, vừa thuận tiện trong suốt thời gian tổ chức.\n\nQuá trình chuẩn bị thường bắt đầu bằng việc ghi nhận địa điểm, thời gian thi công và ngân sách dự kiến. Sau khi thống nhất phương án, các hạng mục sẽ được chuẩn bị, lắp đặt và kiểm tra tổng thể trước thời điểm đón khách.\n\nBạn có thể gửi hình ảnh địa điểm, ngày tổ chức và yêu cầu cụ thể để được tư vấn phương án phù hợp, dễ triển khai và đồng bộ với không gian thực tế.";

        DB::table('events')->orderBy('id')->chunkById(100, function ($events) use ($defaultContent) {
            foreach ($events as $event) {
                DB::table('events')->where('id', $event->id)->update([
                    'after_gallery_title' => 'Kinh nghiệm chuẩn bị cho '.$event->title,
                    'after_gallery_content' => $defaultContent,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['after_gallery_title', 'after_gallery_content']);
        });
    }
};
