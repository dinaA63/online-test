<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('attempt_id')->constrained()->onDelete('cascade');
        $table->foreignId('question_id')->constrained()->onDelete('cascade');
        $table->foreignId('choice_id')->nullable()->constrained()->onDelete('set null');
        $table->boolean('is_correct')->default(false);
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('answers');
}
};
