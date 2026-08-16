<x-dropdown role="user">
    <x-slot:trigger>
        <span class="user-avatar">{{ Illuminate\Support\Str::of(auth()->user()->name ?? 'A')->substr(0, 1) }}</span>
        <span class="user-name">{{ auth()->user()->name ?? '' }}</span>
    </x-slot:trigger>

    <x-dropdown-item icon="user">{{ __('navigation.profile') }}</x-dropdown-item>
    <x-dropdown-item icon="settings">{{ __('navigation.settings') }}</x-dropdown-item>
    <x-dropdown-divider />
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="gdropdown-item destructive" style="width:100%;background:none;border:none;text-align:start;font:inherit;cursor:pointer;">
            <x-icon name="log-out" />
            {{ __('navigation.logout') }}
        </button>
    </form>
</x-dropdown>
