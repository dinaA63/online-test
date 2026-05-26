<x-guest-layout>
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-icon"><i class="fas fa-user-plus"></i></div>
            <h3 class="fw-bold mb-1">Регистрация</h3>
            <p class="text-muted small mb-0">Создайте учётную запись</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label fw-semibold">ФИО</label>
                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Пароль</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label fw-semibold">Подтверждение</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label fw-semibold">Роль</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror">
                    <option value="student" {{ old('role') === 'student' ? 'selected' : '' }}>Студент</option>
                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Преподаватель</option>
                </select>
            </div>
            <div class="mb-3" id="group-select" style="display: {{ old('role', 'student') === 'student' ? 'block' : 'none' }};">
                <label for="group_id" class="form-label fw-semibold">Группа</label>
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
                    Согласен на обработку данных <a href="{{ route('terms') }}" target="_blank">(условия)</a>
                </label>
            </div>
            <button type="submit" class="btn btn-primary w-100 btn-pill">Зарегистрироваться</button>
        </form>
        <p class="text-center text-muted small mt-4 mb-0">
            Уже есть аккаунт? <a href="{{ route('login') }}" style="color: var(--primary);">Войти</a>
        </p>
    </div>
    <script>
        document.getElementById('role').addEventListener('change', function() {
            document.getElementById('group-select').style.display = this.value === 'student' ? 'block' : 'none';
        });
    </script>
</x-guest-layout>
