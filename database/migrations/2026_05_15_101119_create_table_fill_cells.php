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
Schema::create('table_fill_cells', function (Blueprint $table) {
    $table->id();
    $table->foreignId('table_fill_table_id')->constrained()->onDelete('cascade');
    $table->integer('row_index');
    $table->integer('col_index');
    $table->string('expected_answer'); // правильный ответ для этой ячейки
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_fill_cells');
    }
};
