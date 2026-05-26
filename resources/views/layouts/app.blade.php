<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('institution.portal_title'))</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    <div class="institution-topbar">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                <div class="org-name">{{ config('institution.short_name') }} — {{ config('institution.specialty_code') }} · {{ config('institution.groups') }}</div>
                <div class="org-meta text-md-end">
                    <span class="badge badge-gold me-2">5 сем.</span>
                    {{ config('institution.period') }}
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-graduation-cap me-2"></i>Тест-Система
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Меню">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                    @auth
                        @if(Auth::user()->role === 'teacher')
                            <li class="nav-item"><a class="nav-link" href="{{ route('teacher.tests.index') }}"><i class="fas fa-layer-group me-1"></i> Тесты</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('teacher.reviews.index') }}"><i class="fas fa-check-double me-1"></i> Проверка</a></li>
                        @elseif(Auth::user()->role === 'student')
                            <li class="nav-item"><a class="nav-link" href="{{ route('student.tests.index') }}"><i class="fas fa-play me-1"></i> Тесты</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('student.results') }}"><i class="fas fa-chart-line me-1"></i> Результаты</a></li>
                        @endif

                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Админ</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Пользователи</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}">Группы</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.statistics') }}">Статистика</a></li>
                                </ul>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>{{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-id-card me-2"></i>Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        <i class="fas fa-sign-out-alt me-2"></i>Выйти
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Вход</a></li>
                        <li class="nav-item"><a class="nav-link btn btn-primary btn-sm text-white ms-lg-2 px-3" href="{{ route('register') }}">Регистрация</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4 fade-in">
        @if(session('success'))
            <div class="container mb-3">
                <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @if(session('error'))
            <div class="container mb-3">
                <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @if(session('warning'))
            <div class="container mb-3">
                <div class="alert alert-warning alert-dismissible fade show">{{ session('warning') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="footer-premium py-4">
        <div class="container text-center text-muted small">
            © {{ date('Y') }} {{ config('institution.short_name') }} — диагностическая работа
        </div>
    </footer>

    <button id="scrollTopBtn" class="btn-scroll-top" title="Наверх"><i class="fas fa-arrow-up"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>
