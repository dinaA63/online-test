<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'type')) {
                $table->enum('type', ['single_choice', 'multiple_choice', 'text'])->default('single_choice')->after('text');
            }
            if (!Schema::hasColumn('questions', 'points')) {
                $table->integer('points')->default(1)->after('type');
            }
            if (!Schema::hasColumn('questions', 'order')) {
                $table->integer('order')->default(0)->after('points');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'points', 'order']);
        });
    }
};