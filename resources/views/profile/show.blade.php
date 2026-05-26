@extends('layouts.app')

@section('title', 'Мой профиль')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
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

                    <div class="avatar-upload-zone" id="avatar-zone" title="Нажмите, чтобы выбрать фото">
                        @if($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar))
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="Аватар" id="avatar-preview">
                        @else
                            <div class="avatar-placeholder" id="avatar-preview-placeholder"><i class="fas fa-user"></i></div>
                            <img src="" alt="" id="avatar-preview" class="d-none">
                        @endif
                        <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
                    </div>
                    <p class="avatar-hint mb-4">JPEG, PNG, GIF или WebP · до 2 МБ</p>
                    @error('avatar')<div class="text-danger small mb-3">{{ $message }}</div>@enderror

                    <div class="mb-3">
                        <p class="fw-bold mb-0">{{ $user->name }}</p>
                        <p class="text-muted mb-0">{{ $user->email }}</p>
                        <span class="badge rounded-pill mt-2" style="background: var(--primary-soft); color: var(--primary);">{{ $user->role }}</span>
                    </div>

                    <div class="mb-4">
                        <label for="bio" class="form-label fw-semibold">О себе</label>
                        <textarea name="bio" id="bio" rows="4"
                                  class="form-control @error('bio') is-invalid @enderror"
                                  placeholder="Кратко о себе...">{{ old('bio', $user->bio) }}</textarea>
                        @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-pill">
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
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        let img = document.getElementById('avatar-preview');
        const ph = document.getElementById('avatar-preview-placeholder');
        if (!img) {
            img = document.createElement('img');
            img.id = 'avatar-preview';
            document.getElementById('avatar-zone').prepend(img);
        }
        img.src = ev.target.result;
        img.classList.remove('d-none');
        if (ph) ph.remove();
    };
    reader.readAsDataURL(file);
});
</script>
@endpush
