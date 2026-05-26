<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon"><i class="fas fa-key"></i></div>
            <h3 class="fw-bold mb-1">Восстановление пароля</h3>
            <p class="text-muted small mb-0">Ссылка придёт на ваш email</p>
        </div>
        <div class="auth-card-body">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                    </div>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-paper-plane me-2"></i>Отправить ссылку
                </button>
            </form>

            <p class="text-center mt-4 mb-0">
                <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none" style="color: var(--primary);"><i class="fas fa-arrow-left me-1"></i>К входу</a>
            </p>
        </div>
    </div>
</x-guest-layout>
