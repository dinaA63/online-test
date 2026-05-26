@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-10 col-lg-7">
            <div class="stone-card">
                <h2 class="h4 fw-bold mb-4">Профиль</h2>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="profile-form">
                    @csrf

                    <div class="profile-avatar-section text-center mb-4">
                        <x-user-avatar :user="$user" size="xl" id="profile-avatar-display" class="mx-auto" />
                        <label for="avatar" class="btn btn-ghost btn-pill btn-sm mt-3">
                            <i class="fas fa-camera me-1"></i>Выбрать фото
                        </label>
                        <input type="file" name="avatar" id="avatar" class="visually-hidden"
                               accept="image/jpeg,image/png,image/gif,image/webp,image/jpg">
                        <p class="avatar-hint mt-2 mb-0">JPEG, PNG, GIF или WebP · до 2 МБ</p>
                        @error('avatar')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="text-center text-md-start mb-4 pb-4 border-bottom">
                        <p class="fw-bold mb-0">{{ $user->name }}</p>
                        <p class="text-muted mb-0 text-break">{{ $user->email }}</p>
                        <span class="badge-soft badge-soft-info mt-2 d-inline-block">{{ $user->role }}</span>
                    </div>

                    <div class="mb-4">
                        <label for="bio" class="form-label fw-semibold">О себе</label>
                        <textarea name="bio" id="bio" rows="4"
                                  class="form-control @error('bio') is-invalid @enderror"
                                  placeholder="Кратко о себе...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-pill w-100 w-md-auto">
                        <i class="fas fa-save me-2"></i>Сохранить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Файл больше 2 МБ');
        this.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(ev) {
        const wrap = document.getElementById('profile-avatar-display');
        if (!wrap) return;
        let img = wrap.querySelector('.user-avatar__img');
        const fallback = wrap.querySelector('.user-avatar__fallback');
        if (!img) {
            img = document.createElement('img');
            img.className = 'user-avatar__img';
            img.alt = '';
            wrap.insertBefore(img, fallback);
        }
        img.src = ev.target.result;
        fallback?.classList.add('is-hidden');
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
