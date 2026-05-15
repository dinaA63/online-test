<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Система онлайн-тестирования')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    <!-- Шапка сайта с улучшенным дизайном -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-graduation-cap me-2"></i>
                Тест-Система
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end animate-dropdown">
                                @if(Auth::user()->role == 'teacher')
                                    <li><a class="dropdown-item" href="{{ route('teacher.tests.index') }}"><i class="fas fa-tachometer-alt me-2"></i>Мои тесты</a></li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('student.tests.index') }}"><i class="fas fa-list me-2"></i>Тесты</a></li>
                                    <li><a class="dropdown-item" href="{{ route('student.results') }}"><i class="fas fa-chart-line me-2"></i>Мои результаты</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Выйти
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i>Вход</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i>Регистрация</a>
                        </li>
                    @endguest
                    @auth
    @if(Auth::user()->role == 'admin')
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="fas fa-cog"></i> Администрирование
            </a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}"><i class="fas fa-users"></i> Группы</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.statistics') }}"><i class="fas fa-chart-line"></i> Общая статистика</a></li>
            </ul>
        </li>
    @endif
@endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Основной контент с анимацией появления -->
    <main class="py-4 fade-in">
        @yield('content')
    </main>

    <!-- Улучшенный футер -->
    <footer class="footer mt-auto py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <small class="text-muted">© {{ date('Y') }} Система онлайн-тестирования. Все права защищены.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-muted me-3" id="footerHelpLink"><i class="fas fa-question-circle"></i> Помощь</a>
                    <a href="#" class="text-muted"><i class="fas fa-envelope"></i> Контакты</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Кнопка "Вверх" -->
    <button id="scrollTopBtn" class="btn btn-scroll-top" title="Наверх">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
    
    <!-- Небольшой скрипт для обработки клика по "Помощь" (можно убрать или заменить на реальную ссылку) -->
    <script>
        document.getElementById('footerHelpLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            alert('Раздел помощи в разработке. Свяжитесь с администратором.');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>