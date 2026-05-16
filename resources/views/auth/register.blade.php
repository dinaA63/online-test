<x-guest-layout>
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-white text-center pt-4 pb-0 border-0">
            <i class="fas fa-user-plus fa-3x mb-3" style="color: var(--primary);"></i>
            <h3 class="fw-bold">Регистрация</h3>
            <p class="text-muted">Создайте новый аккаунт</p>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">ФИО</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                    </div>
                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
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

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label fw-semibold">Подтверждение пароля</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-check-circle"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="role" class="form-label fw-semibold">Роль</label>
                    <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                        <option value="student">Студент</option>
                        <option value="teacher">Преподаватель</option>
                    </select>
                    @error('role')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3 form-check">
    <input type="checkbox" name="terms" id="terms" class="form-check-input @error('terms') is-invalid @enderror" required>
    <label class="form-check-label" for="terms">
        Я согласен на обработку персональных данных 
        <a href="{{ route('terms') }}" target="_blank">(ознакомиться с условиями)</a>
    </label>
    @error('terms')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>


                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-user-check me-2"></i> Зарегистрироваться
                </button>
            </form>
        </div>
        <div class="card-footer bg-white text-center border-0 pb-4">
            <p class="mb-0">Уже есть аккаунт? <a href="{{ route('login') }}" class="text-decoration-none fw-semibold" style="color: var(--primary);">Войти</a></p>
        </div>
    </div>
</x-guest-layout>