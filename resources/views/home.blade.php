@extends('layouts.app')
@section('title', config('institution.portal_title'))

@section('content')
<div class="container py-2">
    <div class="card diag-hero rounded-4 shadow-lg mb-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge badge-gold mb-3">Диагностическая работа</span>
                    <h1 class="display-6 fw-bold mb-3" style="color: var(--oneap-navy);">{{ config('institution.portal_title') }}</h1>
                    <p class="lead text-muted mb-4">
                        Оценочные материалы для проведения диагностики профессиональных компетенций
                        по специальности {{ config('institution.specialty_code') }} «{{ config('institution.specialty_name') }}».
                    </p>

                    @auth
                        @if(Auth::user()->role === 'teacher')
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('teacher.tests.index') }}" class="btn btn-primary btn-lg"><i class="fas fa-chalkboard-teacher me-2"></i>Управление тестами</a>
                                <a href="{{ route('teacher.gift.import.create') }}" class="btn btn-outline-primary btn-lg"><i class="fas fa-file-import me-2"></i>Импорт GIFT</a>
                            </div>
                        @else
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('student.tests.index') }}" class="btn btn-primary btn-lg"><i class="fas fa-play me-2"></i>Начать диагностику</a>
                                <a href="{{ route('student.results') }}" class="btn btn-outline-primary btn-lg"><i class="fas fa-chart-line me-2"></i>Мои результаты</a>
                            </div>
                        @endif
                    @else
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg"><i class="fas fa-sign-in-alt me-2"></i>Войти</a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg"><i class="fas fa-user-plus me-2"></i>Регистрация</a>
                        </div>
                    @endauth
                </div>
                <div class="col-lg-4 text-center">
                    <div class="icon-circle mx-auto" style="width:120px;height:120px;border-radius:28px;">
                        <i class="fas fa-laptop-code fa-4x" style="color: var(--oneap-burgundy);"></i>
                    </div>
                    <p class="small text-muted mt-3 mb-0">{{ config('institution.short_name') }} · {{ config('institution.semester') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card card-premium h-100">
                <div class="card-header"><i class="fas fa-info-circle me-2"></i>Спецификация работы</div>
                <div class="card-body">
                    <table class="table diag-meta-table mb-0">
                        <tr><th>Специальность</th><td>{{ config('institution.specialty_code') }} {{ config('institution.specialty_name') }}</td></tr>
                        <tr><th>Группы</th><td>{{ config('institution.groups') }}</td></tr>
                        <tr><th>Семестр</th><td>{{ config('institution.semester') }}</td></tr>
                        <tr><th>Период</th><td>{{ config('institution.period') }}</td></tr>
                        <tr><th>Время выполнения</th><td>{{ config('institution.test_duration_minutes') }} минут · до {{ config('institution.max_primary_score') }} первичных баллов</td></tr>
                        <tr><th>Разработчики</th><td>{{ config('institution.developers') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card card-premium h-100">
                <div class="card-header"><i class="fas fa-list-check me-2"></i>Типы заданий</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex gap-3">
                            <span class="badge bg-light text-dark border">1</span>
                            <div><strong>Соответствие</strong> — установление связей между понятиями</div>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <span class="badge bg-light text-dark border">2</span>
                            <div><strong>Последовательность</strong> — расстановка шагов в правильном порядке</div>
                        </li>
                        <li class="mb-3 d-flex gap-3">
                            <span class="badge bg-light text-dark border">3</span>
                            <div><strong>Выбор ответа</strong> — один или несколько вариантов с обоснованием</div>
                        </li>
                        <li class="d-flex gap-3">
                            <span class="badge bg-light text-dark border">4</span>
                            <div><strong>Развёрнутый ответ</strong> — ручная проверка преподавателем</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-premium mb-4">
        <div class="card-header"><i class="fas fa-scale-balanced me-2"></i>Система оценивания (первичные баллы)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table grading-scale-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">Первичные баллы</th>
                            <th>Оценка</th>
                            <th class="pe-4">Уровень освоения</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(config('institution.grading_scale') as $row)
                            <tr>
                                <td class="ps-4">{{ $row['min'] }} – {{ $row['max'] }}</td>
                                <td><strong>{{ $row['grade'] }}</strong></td>
                                <td class="pe-4">{{ $row['level'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <p class="text-center text-muted small mb-0">{{ config('institution.pcc_protocol') }}</p>
</div>
@endsection
