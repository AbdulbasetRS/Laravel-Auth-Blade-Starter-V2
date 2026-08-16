<div class="admin-navbar">
    <div class="admin-navbar-left">
        <button class="sidebar-toggle-btn" id="sidebarToggle" type="button" aria-label="{{ __('common.toggle_sidebar') }}">
            <x-icon name="menu" />
        </button>
        <div class="admin-page-title">@yield('page-title', __('navigation.admin_panel'))</div>
    </div>

    <div class="admin-navbar-center">
        <x-admin.clock />
    </div>

    <div class="admin-navbar-right">
        <x-admin.user-menu />
    </div>
</div>
