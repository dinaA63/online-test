<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('users', function (Blueprint $table) {
            // если нужно изменить возможные значения enum (в случае использования enum)
            // или просто обновить тип
            $table->string('role')->default('student')->change();
        });
        // Добавить администратора вручную через seeder или напрямую в БД
        \App\Models\User::updateOrCreate(
            ['email' => 'admin@test.com'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role' => 'admin']
        );
    }
    public function down() {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('student')->change();
        });
    }
};