<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TestController;
use App\Http\Controllers\Teacher\QuestionController;
use App\Http\Controllers\Teacher\ChoiceController;
use App\Http\Controllers\Student\TestController as StudentTestController;
use App\Http\Controllers\Student\AttemptController;

// Главная страница
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->role == 'teacher' 
            ? redirect()->route('teacher.tests.index') 
            : redirect()->route('student.tests.index');
    }
    return view('home');
})->name('home');

// Маршруты аутентификации Breeze (включая восстановление пароля)
require __DIR__.'/auth.php';

// Группа маршрутов для преподавателя
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::resource('tests', TestController::class);
    Route::get('tests/{test}/questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('tests/{test}/questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/edit', [QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('questions/{question}', [QuestionController::class, 'update'])->name('questions.update');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('questions/{question}/choices', [ChoiceController::class, 'store'])->name('choices.store');
    Route::get('choices/{choice}/edit', [ChoiceController::class, 'edit'])->name('choices.edit');
    Route::put('choices/{choice}', [ChoiceController::class, 'update'])->name('choices.update');
    Route::delete('choices/{choice}', [ChoiceController::class, 'destroy'])->name('choices.destroy');
    Route::get('tests/{test}/statistics', [TestController::class, 'statistics'])->name('tests.statistics');
    Route::get('tests/{test}/export', [TestController::class, 'export'])->name('tests.export');
});

// Группа маршрутов для студента
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('tests', [StudentTestController::class, 'index'])->name('tests.index');
    Route::get('tests/{test}', [StudentTestController::class, 'show'])->name('tests.show');
    Route::post('tests/{test}/attempt', [AttemptController::class, 'start'])->name('attempt.start');
    Route::get('attempt/{attempt}', [AttemptController::class, 'show'])->name('attempt.show');
    Route::post('attempt/{attempt}/save-answer', [AttemptController::class, 'saveAnswer'])->name('attempt.save_answer');
    Route::post('attempt/{attempt}/submit', [AttemptController::class, 'submit'])->name('attempt.submit');
    Route::get('results', [AttemptController::class, 'history'])->name('results');
});