<aside class="admin-sidebar" id="adminSidebar">
    <nav class="sidebar-nav">
        <a href="{{ route('admin.test') }}" class="sidebar-item {{ request()->routeIs('admin.test') ? 'active' : '' }}">
            <x-icon name="grid" />
            <span class="sidebar-label">{{ __('navigation.dashboard') }}</span>
        </a>

        <div class="sidebar-group {{ request()->routeIs('admin.users.*') ? 'open' : '' }}">
            <button type="button" class="sidebar-item sidebar-group-toggle" aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
                <x-icon name="users" />
                <span class="sidebar-label">{{ __('navigation.user_management') }}</span>
                <x-icon name="chevron-down" class="sidebar-chevron" />
            </button>
            <div class="sidebar-submenu">
                <a href="{{ route('admin.users.index') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.index') ? 'active' : '' }}">
                    {{ __('navigation.users') }}
                </a>
                <a href="{{ route('admin.users.create') }}" class="sidebar-subitem {{ request()->routeIs('admin.users.create') ? 'active' : '' }}">
                    {{ __('navigation.create_user') }}
                </a>
            </div>
        </div>

        {{-- Roles & Permissions module isn't built yet — placeholder links,
             matches what was approved in the preview stage. --}}
        <div class="sidebar-group">
            <button type="button" class="sidebar-item sidebar-group-toggle" aria-expanded="false">
                <x-icon name="shield" />
                <span class="sidebar-label">{{ __('navigation.roles') }}</span>
                <x-icon name="chevron-down" class="sidebar-chevron" />
            </button>
            <div class="sidebar-submenu">
                <a href="#" class="sidebar-subitem">{{ __('navigation.create_role') }}</a>
                <a href="#" class="sidebar-subitem">{{ __('navigation.permissions') }}</a>
            </div>
        </div>
    </nav>
</aside>
