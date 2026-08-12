<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('category_content_blocks')
            ->join('category_pages', 'category_pages.id', '=', 'category_content_blocks.category_page_id')
            ->join('categories', 'categories.id', '=', 'category_pages.category_id')
            ->whereNotNull('category_content_blocks.image')
            ->where(function ($query) {
                $query->whereNull('category_content_blocks.image_alt')
                    ->orWhere('category_content_blocks.image_alt', '');
            })
            ->update(['category_content_blocks.image_alt' => DB::raw('categories.name')]);
    }

    public function down(): void
    {
        // Backfilled Alt text is intentionally retained.
    }
};
