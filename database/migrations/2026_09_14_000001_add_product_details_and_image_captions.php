<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('content_title')->nullable()->after('content');
            $table->json('price_details')->nullable()->after('sale_price');
        });

        Schema::table('category_pages', function (Blueprint $table) {
            $table->string('banner_caption')->nullable()->after('banner_alt');
            $table->string('service_image_caption')->nullable()->after('service_image_alt');
        });

        Schema::table('category_content_blocks', function (Blueprint $table) {
            $table->string('image_caption')->nullable()->after('image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('category_content_blocks', fn (Blueprint $table) => $table->dropColumn('image_caption'));
        Schema::table('category_pages', fn (Blueprint $table) => $table->dropColumn(['banner_caption', 'service_image_caption']));
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn(['content_title', 'price_details']));
    }
};
