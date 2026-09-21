<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'events_status_created_at_index');
            $table->index(['category_id', 'status', 'created_at'], 'events_category_status_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_created_at_index');
            $table->dropIndex('events_category_status_created_at_index');
        });
    }
};
