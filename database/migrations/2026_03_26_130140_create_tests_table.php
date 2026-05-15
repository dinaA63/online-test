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
    Schema::create('tests', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
        $table->integer('time_limit')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
public function down()
{
    Schema::dropIfExists('tests');
}
};
