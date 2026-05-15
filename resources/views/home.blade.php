@extends('layouts.app')
@section('title', 'Главная')

@section('content')
<div class="container">
    <!-- Hero Section с градиентом -->
    <div class="row justify-content-center text-center py-5">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 bg-gradient-hero rounded-4 shadow-lg overflow-hidden">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="fas fa-graduation-cap fa-4x" style="color: var(--primary);"></i>
                        </div>
                    </div>
                    <h1 class="display-5 fw-bold mb-3" style="color: var(--primary-dark);">Система онлайн-тестирования</h1>
                    <p class="lead text-muted mb-4">Создавайте тесты, проходите экзамены и получайте мгновенную статистику результатов в современном интерфейсе.</p>

                    @auth
                        @if(Auth::user()->role == 'teacher')
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{ route('teacher.tests.index') }}" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-chalkboard-teacher me-2"></i>Мои тесты
                                </a>
                                <a href="{{ route('teacher.tests.create') }}" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-plus me-2"></i>Создать тест
                                </a>
                            </div>
                        @else
                            <div class="d-flex flex-wrap justify-content-center gap-3">
                                <a href="{{ route('student.tests.index') }}" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-list me-2"></i>Доступные тесты
                                </a>
                                <a href="{{ route('student.results') }}" class="btn btn-outline-primary btn-lg px-4">
                                    <i class="fas fa-chart-line me-2"></i>Мои результаты
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-sign-in-alt me-2"></i>Войти
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-primary btn-lg px-4">
                                <i class="fas fa-user-plus me-2"></i>Регистрация
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section с улучшенными карточками -->
    <div class="row mt-5 g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center feature-card">
                <div class="card-body p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-chalkboard-teacher fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold">Для преподавателей</h5>
                    <p class="card-text text-muted">Создавайте тесты с разными типами вопросов, управляйте попытками и просматривайте подробную статистику.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center feature-card">
                <div class="card-body p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-user-graduate fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold">Для студентов</h5>
                    <p class="card-text text-muted">Проходите тесты, получайте автоматическую проверку, отслеживайте свои результаты.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 text-center feature-card">
                <div class="card-body p-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-chart-bar fa-3x"></i>
                    </div>
                    <h5 class="card-title fw-bold">Автоматическая статистика</h5>
                    <p class="card-text text-muted">Мгновенный расчёт баллов, графики успеваемости, экспорт данных в CSV.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Дополнительный блок с преимуществами (по желанию) -->
    <div class="row mt-5 justify-content-center">
        <div class="col-md-8 text-center">
            <div class="p-4 bg-light rounded-4">
                <i class="fas fa-rocket fa-2x mb-2" style="color: var(--primary);"></i>
                <h4 class="fw-semibold">Готовы начать?</h4>
                <p class="text-muted">Присоединяйтесь к тысячам пользователей, которые уже используют нашу платформу для обучения и проверки знаний.</p>
            </div>
        </div>
    </div>

    <div class="row mt-4 mb-4">
        <div class="col text-center text-muted">
            <small>© {{ date('Y') }} Система онлайн-тестирования. Все права защищены.</small>
        </div>
    </div>
</div>
@endsection