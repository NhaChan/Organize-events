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
            $table->string('thumbnail_alt')->nullable()->after('thumbnail');
        });

        DB::table('events')
            ->whereNotNull('thumbnail')
            ->whereNull('thumbnail_alt')
            ->update(['thumbnail_alt' => DB::raw('title')]);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('thumbnail_alt');
        });
    }
};
