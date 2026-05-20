<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        // Убеждаемся, что колонка 'role' существует и имеет тип string
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student');
            } else {
                // Если колонка уже есть, просто меняем тип (требуется doctrine/dbal)
                $table->string('role')->default('student')->change();
            }
        });

        // Добавляем администратора (если ещё не существует)
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