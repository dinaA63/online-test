@props(['user', 'size' => 'md', 'class' => ''])

@php
    use App\Support\Avatar;
    $url = Avatar::url($user);
    $initials = Avatar::initials($user);
    $sizeClass = match ($size) {
        'sm' => 'user-avatar--sm',
        'lg' => 'user-avatar--lg',
        'xl' => 'user-avatar--xl',
        default => 'user-avatar--md',
    };
@endphp

<div {{ $attributes->merge(['class' => "user-avatar {$sizeClass} {$class}"]) }}>
    @if($url)
        <img src="{{ $url }}" alt="" class="user-avatar__img" loading="lazy"
             onerror="this.remove(); this.parentElement.querySelector('.user-avatar__fallback')?.classList.remove('is-hidden');">
    @endif
    <span class="user-avatar__fallback {{ $url ? 'is-hidden' : '' }}" aria-hidden="true">{{ $initials }}</span>
</div>
