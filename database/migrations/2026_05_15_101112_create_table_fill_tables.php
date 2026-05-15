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
Schema::create('table_fill_tables', function (Blueprint $table) {
    $table->id();
    $table->foreignId('question_id')->constrained()->onDelete('cascade');
    $table->json('headers');      // массив названий столбцов
    $table->json('rows');         // массив строк (или количество строк)
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_fill_tables');
    }
};
