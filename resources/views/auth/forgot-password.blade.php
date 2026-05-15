<x-guest-layout>
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-white text-center pt-4 pb-0 border-0">
            <i class="fas fa-key fa-3x mb-3" style="color: var(--primary);"></i>
            <h3 class="fw-bold">Восстановление пароля</h3>
            <p class="text-muted">Введите ваш email, и мы отправим ссылку для сброса пароля</p>
        </div>
        <div class="card-body p-4">
            <!-- Session Status -->
            @if (session('status'))
                <div class="alert alert-success mb-3">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('login') }}" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Вернуться к входу
                    </a>
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-paper-plane me-2"></i> Отправить
                    </button>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white text-center border-0 pb-4">
            <p class="mb-0">Вспомнили пароль? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--primary);">Войти</a></p>
        </div>
    </div>
</x-guest-layout>