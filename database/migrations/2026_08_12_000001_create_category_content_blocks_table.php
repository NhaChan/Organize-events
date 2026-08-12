<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_page_id')->constrained()->cascadeOnDelete();
            $table->string('heading')->nullable();
            $table->longText('content')->nullable();
            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('category_pages')
            ->where(function ($query) {
                $query->whereNotNull('description')->orWhereNotNull('service_image');
            })
            ->orderBy('id')
            ->each(function ($page) {
                DB::table('category_content_blocks')->insert([
                    'category_page_id' => $page->id,
                    'heading' => $page->page_title,
                    'content' => $page->description,
                    'image' => $page->service_image,
                    'image_alt' => $page->service_image_alt,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_content_blocks');
    }
};
