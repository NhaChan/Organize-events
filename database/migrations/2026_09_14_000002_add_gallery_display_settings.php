<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('thumbnail_fit', 10)->default('cover')->after('thumbnail_alt');
            $table->unsignedTinyInteger('thumbnail_position_y')->default(50)->after('thumbnail_fit');
        });

        Schema::table('event_images', function (Blueprint $table) {
            $table->string('display_fit', 10)->default('cover')->after('alt_text');
            $table->unsignedTinyInteger('position_y')->default(50)->after('display_fit');
        });
    }

    public function down(): void
    {
        Schema::table('event_images', fn (Blueprint $table) => $table->dropColumn(['display_fit', 'position_y']));
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn(['thumbnail_fit', 'thumbnail_position_y']));
    }
};
