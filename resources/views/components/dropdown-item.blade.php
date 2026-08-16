@props(['selected' => false, 'destructive' => false, 'icon' => null])
<div {{ $attributes->merge(['class' => 'gdropdown-item'.($selected ? ' selected' : '').($destructive ? ' destructive' : ''), 'role' => 'menuitem']) }}>
    @if($icon)
        <x-icon :name="$icon" />
    @else
        <x-icon name="check" class="check" />
    @endif
    {{ $slot }}
</div>
