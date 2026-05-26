<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('institution.short_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-stone sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('institution.short_name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-label="Меню">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    @auth
                        @if(Auth::user()->role === 'teacher')
                            <li class="nav-item"><a class="nav-link nav-pill" href="{{ route('teacher.tests.index') }}">Тесты</a></li>
                            <li class="nav-item"><a class="nav-link nav-pill" href="{{ route('teacher.reviews.index') }}">Проверка</a></li>
                        @elseif(Auth::user()->role === 'student')
                            <li class="nav-item"><a class="nav-link nav-pill" href="{{ route('student.tests.index') }}">Тесты</a></li>
                            <li class="nav-item"><a class="nav-link nav-pill" href="{{ route('student.results') }}">Результаты</a></li>
                        @endif
                        @if(Auth::user()->role === 'admin')
                            <li class="nav-item dropdown">
                                <a class="nav-link nav-pill dropdown-toggle" href="#" data-bs-toggle="dropdown">Админ</a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Пользователи</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.groups.index') }}">Группы</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.statistics') }}">Статистика</a></li>
                                </ul>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link nav-pill dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                @if(Auth::user()->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists(Auth::user()->avatar))
                                    <img src="{{ asset('storage/'.Auth::user()->avatar) }}" alt="" class="nav-avatar">
                                @else
                                    <span class="nav-avatar nav-avatar-placeholder"><i class="fas fa-user"></i></span>
                                @endif
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">Профиль</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Выйти</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link nav-pill" href="{{ route('login') }}">Вход</a></li>
                        <li class="nav-item"><a class="nav-link nav-pill nav-pill-active" href="{{ route('register') }}">Регистрация</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="fade-in">
        @if(session('success'))
            <div class="container pt-3"><div class="alert alert-success">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="container pt-3"><div class="alert alert-danger">{{ session('error') }}</div></div>
        @endif
        @if(session('warning'))
            <div class="container pt-3"><div class="alert alert-warning">{{ session('warning') }}</div></div>
        @endif
        @yield('content')
    </main>

    <footer class="footer-stone">
        <div class="container text-center text-muted small py-4">
            © {{ date('Y') }} {{ config('institution.short_name') }} · {{ config('institution.portal_title') }}
        </div>
    </footer>

    <button id="scrollTopBtn" class="btn-scroll-top" title="Наверх"><i class="fas fa-arrow-up"></i></button>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')
</body>
</html>
