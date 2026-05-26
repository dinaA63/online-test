@props(['title', 'label' => null])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div class="page-header-text">
        @if($label)
            <p class="page-label">{{ $label }}</p>
        @endif
        <h1 class="page-title">{{ $title }}</h1>
    </div>
    @if(isset($actions))
        <div class="page-actions">{{ $actions }}</div>
    @endif
</div>
