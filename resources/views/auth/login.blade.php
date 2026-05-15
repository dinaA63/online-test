<x-guest-layout>
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-white text-center pt-4 pb-0 border-0">
            <i class="fas fa-graduation-cap fa-3x mb-3" style="color: var(--primary);"></i>
            <h3 class="fw-bold">Добро пожаловать</h3>
            <p class="text-muted">Войдите в свою учётную запись</p>
        </div>
        <div class="card-body p-4">
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Пароль</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    </div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label" for="remember">Запомнить меня</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none small">Забыли пароль?</a>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-arrow-right-to-bracket me-2"></i> Войти
                </button>
            </form>
        </div>
        <div class="card-footer bg-white text-center border-0 pb-4">
            <p class="mb-0">Нет аккаунта? <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--primary);">Зарегистрироваться</a></p>
        </div>
    </div>
</x-guest-layout>