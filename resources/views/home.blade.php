@extends('layouts.app')
@section('title', config('institution.portal_title'))

@section('content')
<div class="container py-5">
    <section class="hero-stone mb-5">
        <p class="hero-label">Онлайн-тестирование</p>
        <h1 class="hero-title">{{ config('institution.portal_title') }}</h1>
        <p class="hero-lead">{{ config('institution.short_name') }} — оценочные материалы для диагностики профессиональных компетенций</p>

        @auth
            @if(Auth::user()->role === 'teacher')
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('teacher.tests.index') }}" class="btn btn-primary btn-pill">Мои тесты</a>
                    <a href="{{ route('teacher.gift.import.create') }}" class="btn btn-ghost btn-pill">Импорт GIFT</a>
                </div>
            @else
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="{{ route('student.tests.index') }}" class="btn btn-primary btn-pill">Пройти диагностику</a>
                    <a href="{{ route('student.results') }}" class="btn btn-ghost btn-pill">Мои результаты</a>
                </div>
            @endif
        @else
            <div class="d-flex flex-wrap gap-3 mt-4">
                <a href="{{ route('login') }}" class="btn btn-primary btn-pill">Войти</a>
                <a href="{{ route('register') }}" class="btn btn-ghost btn-pill">Регистрация</a>
            </div>
        @endauth
    </section>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="stone-card h-100">
                <span class="stone-num">01</span>
                <h3>Соответствие</h3>
                <p class="text-muted mb-0">Установление связей между понятиями и определениями</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stone-card h-100">
                <span class="stone-num">02</span>
                <h3>Последовательность</h3>
                <p class="text-muted mb-0">Расстановка шагов и этапов в правильном порядке</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stone-card h-100">
                <span class="stone-num">03</span>
                <h3>Открытые ответы</h3>
                <p class="text-muted mb-0">Развёрнутые ответы с ручной проверкой преподавателем</p>
            </div>
        </div>
    </div>

    <div class="stone-card mt-4">
        <h3 class="h5 mb-3">Шкала оценивания</h3>
        <div class="table-responsive">
            <table class="table table-minimal mb-0">
                <thead>
                    <tr><th>Баллы</th><th>Оценка</th><th>Уровень</th></tr>
                </thead>
                <tbody>
                    @foreach(config('institution.grading_scale') as $row)
                        <tr>
                            <td>{{ $row['min'] }} – {{ $row['max'] }}</td>
                            <td><strong>{{ $row['grade'] }}</strong></td>
                            <td class="text-muted">{{ $row['level'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
