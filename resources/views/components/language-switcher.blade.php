{{-- Language is a real navigation link (mcamara-generated localized URL),
     not a JS-simulated text swap — the whole page re-renders server-side. --}}
@php
    $supported = LaravelLocalization::getSupportedLocales();
    $current = LaravelLocalization::getCurrentLocale();
@endphp
<x-dropdown variant="light" role="language">
    <x-slot:trigger>
        <x-icon name="globe" class="lang-icon" />
        <span>{{ $supported[$current]['native'] }}</span>
    </x-slot:trigger>
    @foreach($supported as $localeCode => $properties)
        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
           class="gdropdown-item {{ $localeCode === $current ? 'selected' : '' }}"
           role="menuitem">
            <x-icon name="check" class="check" />
            {{ $properties['native'] }}
        </a>
    @endforeach
</x-dropdown>
