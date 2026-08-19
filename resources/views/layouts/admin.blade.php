<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
      dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
      data-theme="light" id="htmlRoot">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name')) — {{ __('navigation.admin_panel') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800&family=Cairo:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/admin-tables.css') }}">
<script>
  // Avoid a theme flash on load: read the persisted preference before first paint
  (function () {
    var m = document.cookie.match(/(?:^|; )theme=(dark|light)/);
    if (m && m[1] === 'dark') { document.documentElement.setAttribute('data-theme', 'dark'); }
  })();
</script>
@stack('styles')
</head>
<body>
<div class="admin-shell">
    <x-admin.navbar />
    <div class="admin-body">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <x-admin.sidebar />
        <div class="admin-content-col">
            <main class="admin-main">
                @yield('content')
            </main>
            <x-admin.footer />
        </div>
    </div>
</div>

<x-confirmation-modal />
<x-toast-container />

<script src="{{ asset('assets/js/dropdown.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/admin.js') }}"></script>
<script src="{{ asset('assets/js/confirmation-modal.js') }}"></script>
<script src="{{ asset('assets/js/toast.js') }}"></script>
@stack('scripts')
</body>
</html>
