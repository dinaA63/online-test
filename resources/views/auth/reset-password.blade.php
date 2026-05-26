<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon"><i class="fas fa-shield-halved"></i></div>
            <h3 class="fw-bold mb-1">Новый пароль</h3>
            <p class="text-muted small mb-0">Придумайте надёжный пароль</p>
        </div>
        <div class="auth-card-body">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $request->email) }}" required>
                    @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Новый пароль</label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Подтверждение</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">Сохранить пароль</button>
            </form>
        </div>
    </div>
</x-guest-layout>
