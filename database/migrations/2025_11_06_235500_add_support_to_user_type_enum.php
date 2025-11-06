<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the ENUM to include 'support'
        // MySQL requires ALTER TABLE to modify ENUM values
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `user_type` ENUM('admin', 'student', 'teacher', 'support') DEFAULT 'student'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove 'support' from ENUM (but first check if any support users exist)
        // If support users exist, you might want to convert them to another type first
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `user_type` ENUM('admin', 'student', 'teacher') DEFAULT 'student'");
    }
};

