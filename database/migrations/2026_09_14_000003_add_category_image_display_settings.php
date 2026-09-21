<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_pages', function (Blueprint $table) {
            $table->string('service_image_fit', 10)->default('cover')->after('service_image_caption');
            $table->unsignedTinyInteger('service_image_position_y')->default(50)->after('service_image_fit');
            $table->string('banner_image_fit', 10)->default('cover')->after('banner_caption');
            $table->unsignedTinyInteger('banner_image_position_y')->default(50)->after('banner_image_fit');
        });

        Schema::table('category_content_blocks', function (Blueprint $table) {
            $table->string('image_fit', 10)->default('cover')->after('image_caption');
            $table->unsignedTinyInteger('image_position_y')->default(50)->after('image_fit');
        });
    }

    public function down(): void
    {
        Schema::table('category_content_blocks', fn (Blueprint $table) => $table->dropColumn(['image_fit', 'image_position_y']));
        Schema::table('category_pages', fn (Blueprint $table) => $table->dropColumn(['service_image_fit', 'service_image_position_y', 'banner_image_fit', 'banner_image_position_y']));
    }
};
