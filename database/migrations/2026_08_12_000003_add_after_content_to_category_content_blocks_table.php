<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('category_content_blocks', function (Blueprint $table) {
            $table->longText('after_content')->nullable()->after('image_alt');
        });
    }

    public function down(): void
    {
        Schema::table('category_content_blocks', function (Blueprint $table) {
            $table->dropColumn('after_content');
        });
    }
};
