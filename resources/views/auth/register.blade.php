<x-guest-layout>
    <div class="auth-card auth-card--wide">
        <div class="auth-card-header">
            <div class="auth-icon"><i class="fas fa-user-plus"></i></div>
            <h3 class="fw-bold mb-1">Регистрация</h3>
            <p class="text-muted small mb-0">Создайте учётную запись</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">ФИО</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user text-muted"></i></span>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required autofocus autocomplete="name">
                </div>
                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required autocomplete="email">
                </div>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>

            <div class="row g-3">
                <div class="col-12 col-sm-6">
                    <label for="password" class="form-label">Пароль</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" id="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    </div>
                    @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 col-sm-6">
                    <label for="password_confirmation" class="form-label">Подтверждение</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check text-muted"></i></span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="form-control" required autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="mb-3 mt-3">
                <label for="role" class="form-label">Роль</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>Студент</option>
                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Преподаватель</option>
                </select>
            </div>

            <div class="mb-3" id="group-select" style="display: {{ old('role', 'student') === 'student' ? 'block' : 'none' }};">
                <label for="group_id" class="form-label">Группа</label>
                <select name="group_id" id="group_id" class="form-select">
                    <option value="">Без группы</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" {{ old('group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4 form-check">
                <input type="checkbox" name="terms" id="terms" class="form-check-input @error('terms') is-invalid @enderror" required>
                <label class="form-check-label small" for="terms">
                    Согласен на обработку данных <a href="{{ route('terms') }}" target="_blank" rel="noopener">(условия)</a>
                </label>
                @error('terms')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <button type="submit" class="btn btn-primary w-100 btn-pill">Зарегистрироваться</button>
        </form>

        <p class="text-center text-muted small mt-4 mb-0">
            Уже есть аккаунт? <a href="{{ route('login') }}" class="fw-semibold text-decoration-none" style="color: var(--primary);">Войти</a>
        </p>
    </div>
    <script>
        document.getElementById('role')?.addEventListener('change', function() {
            document.getElementById('group-select').style.display = this.value === 'student' ? 'block' : 'none';
        });
    </script>
</x-guest-layout>
