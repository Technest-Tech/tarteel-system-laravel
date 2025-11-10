<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->string('series_id', 64)->nullable()->after('id');
            $table->string('color', 32)->nullable()->after('lesson_name');

            $table->index('series_id', 'timetable_series_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timetable', function (Blueprint $table) {
            $table->dropIndex('timetable_series_id_idx');
            $table->dropColumn(['series_id', 'color']);
        });
    }
};
