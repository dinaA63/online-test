<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('matching_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('left_text');      // текст левого элемента (например, "Столица Франции")
            $table->string('right_text');     // текст правого элемента (например, "Париж")
            $table->integer('order')->default(0); // порядок отображения
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('matching_pairs'); }
};