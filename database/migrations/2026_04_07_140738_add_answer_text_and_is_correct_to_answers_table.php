<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            if (!Schema::hasColumn('answers', 'answer_text')) {
                $table->text('answer_text')->nullable()->after('choice_id');
            }
            if (!Schema::hasColumn('answers', 'is_correct')) {
                $table->boolean('is_correct')->default(false)->after('answer_text');
            }
        });
    }

    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropColumn(['answer_text', 'is_correct']);
        });
    }
};