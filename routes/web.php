<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TestController;
use App\Http\Controllers\Teacher\QuestionController;
use App\Http\Controllers\Teacher\ChoiceController;
use App\Http\Controllers\Student\TestController as StudentTestController;
use App\Http\Controllers\Student\AttemptController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/*
|--------------------------------------------------------------------------
| Главная страница
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    if (auth()->check()) {
        $role = auth()->user()->role;

        if ($role === 'teacher') {
            return redirect()->route('teacher.tests.index');
        }

        if ($role === 'admin') {
            return redirect()->route('admin.statistics');
        }

        // Для роли student (и любых других) перенаправляем на список тестов студента
        return redirect()->route('student.tests.index');
    }
    return view('home');
})->name('home');

/*
|--------------------------------------------------------------------------
| Маршруты аутентификации (Breeze)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Группа маршрутов для преподавателя (role: teacher)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    // Управление тестами
    Route::resource('tests', TestController::class);
    // Импорт GIFT
    Route::get('gift/import', [\App\Http\Controllers\Teacher\GiftImportController::class, 'create'])->name('gift.import.create');
    Route::post('gift/import', [\App\Http\Controllers\Teacher\GiftImportController::class, 'store'])->name('gift.import');
    // Управление вопросами
    Route::get('tests/{test}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('tests/{test}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');

    // Управление вариантами ответов
    Route::post('questions/{question}/choices', [ChoiceController::class, 'store'])->name('choices.store');
    Route::get('choices/{choice}/edit', [ChoiceController::class, 'edit'])->name('choices.edit');
    Route::put('choices/{choice}', [ChoiceController::class, 'update'])->name('choices.update');
    Route::delete('choices/{choice}', [ChoiceController::class, 'destroy'])->name('choices.destroy');

    // Статистика и экспорт (для конкретного теста)
    Route::get('tests/{test}/statistics', [TestController::class, 'statistics'])->name('tests.statistics');
    Route::get('tests/{test}/export/csv', [TestController::class, 'exportCsv'])->name('tests.export.csv');
    Route::get('tests/{test}/export/excel', [TestController::class, 'exportExcel'])->name('tests.export.excel');

    // Ручная проверка ответов
    Route::get('reviews', [\App\Http\Controllers\Teacher\ManualReviewController::class, 'index'])->name('reviews.index');
    Route::get('reviews/{attempt}', [\App\Http\Controllers\Teacher\ManualReviewController::class, 'show'])->name('reviews.show');
    Route::post('reviews/{attempt}', [\App\Http\Controllers\Teacher\ManualReviewController::class, 'review'])->name('reviews.review');
});

/*
|--------------------------------------------------------------------------
| Группа маршрутов для администратора (role: admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->except(['create', 'store', 'show']);
    Route::resource('groups', \App\Http\Controllers\Admin\GroupController::class)->except(['show']);  // ← добавлено
    Route::get('statistics', [\App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics');
    Route::get('statistics/export-csv', [\App\Http\Controllers\Admin\StatisticsController::class, 'exportCsv'])->name('statistics.export.csv');
    Route::get('statistics/export-excel', [\App\Http\Controllers\Admin\StatisticsController::class, 'exportExcel'])->name('statistics.export.excel');
});

/*
|--------------------------------------------------------------------------
| Профиль пользователя
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [\App\Http\Controllers\UserProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile', [\App\Http\Controllers\UserProfileController::class, 'update'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Группа маршрутов для студента (role: student)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('tests', [StudentTestController::class, 'index'])->name('tests.index');
    Route::get('tests/{test}', [StudentTestController::class, 'show'])->name('tests.show');
    Route::post('tests/{test}/attempt', [AttemptController::class, 'start'])->name('attempt.start');
    Route::get('attempt/{attempt}', [AttemptController::class, 'show'])->name('attempt.show');
    Route::post('attempt/{attempt}/save-answer', [AttemptController::class, 'saveAnswer'])->name('attempt.save_answer');
    Route::post('attempt/{attempt}/submit', [AttemptController::class, 'submit'])->name('attempt.submit');
    Route::get('results', [AttemptController::class, 'history'])->name('results');
});

/*
|--------------------------------------------------------------------------
| Прочие маршруты
|--------------------------------------------------------------------------
*/
Route::get('/terms', [App\Http\Controllers\TermsController::class, 'show'])->name('terms');

Route::get('/db-test', function () {
    try {
        DB::connection()->getPdo();
        return 'Database connected successfully!';
    } catch (\Exception $e) {
        return 'Database error: ' . $e->getMessage();
    }
});