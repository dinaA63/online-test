<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon"><i class="fas fa-graduation-cap"></i></div>
            <h3 class="fw-bold mb-1">Добро пожаловать</h3>
            <p class="text-muted small mb-0">Войдите в учётную запись</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autofocus autocomplete="email">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required autocomplete="current-password">
                </div>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-4 form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label small" for="remember">Запомнить меня</label>
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-pill">Войти</button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Нет аккаунта? <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color: var(--primary);">Регистрация</a>
        </p>
    </div>
</x-guest-layout>
