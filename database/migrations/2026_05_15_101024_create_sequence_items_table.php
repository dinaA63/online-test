<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('sequence_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('item_text');      // текст элемента (например, "Вскипятить воду")
            $table->integer('correct_order'); // правильная позиция (1,2,3...)
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('sequence_items'); }
};