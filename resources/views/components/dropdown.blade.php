{{--
    Global Dropdown Component — the ONLY dropdown shell used anywhere in the
    project (language switcher, user menu, filters, row actions, exports...).
    Variants share this exact markup/CSS; differences are content only.
--}}
@props(['variant' => 'dark', 'role' => null, 'selectStyle' => false])
<div {{ $attributes->merge(['class' => 'gdropdown'.($selectStyle ? ' select-dropdown' : '')]) }} @if($role) data-role="{{ $role }}" @endif>
    <button type="button" class="gdropdown-trigger{{ $variant === 'light' ? ' light' : '' }}" aria-haspopup="true" aria-expanded="false">
        {{ $trigger }}
        <x-icon name="chevron-down" class="chev" />
    </button>
    <div class="gdropdown-menu" role="menu">
        {{ $slot }}
    </div>
</div>
