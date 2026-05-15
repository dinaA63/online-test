<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('drag_drop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->string('item_text');            // элемент для перетаскивания
            $table->string('target_zone');          // название зоны, куда нужно перетащить
            $table->string('correct_zone');         // правильная зона (обычно совпадает с target_zone)
            $table->timestamps();
        });
    }
    public function down() { Schema::dropIfExists('drag_drop_items'); }
};