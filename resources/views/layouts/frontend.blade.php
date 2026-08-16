<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
      dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
      data-theme="light" id="htmlRoot">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', config('app.name'))</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800&family=Cairo:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/frontend.css') }}">
<script>
  (function () {
    var m = document.cookie.match(/(?:^|; )theme=(dark|light)/);
    if (m && m[1] === 'dark') { document.documentElement.setAttribute('data-theme', 'dark'); }
  })();
</script>
</head>
<body>
<header class="fe-header">
    <div class="fe-brand"><span class="dot"></span> {{ config('app.name') }}</div>
    <nav class="fe-nav">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">{{ __('navigation.home') }}</a>
        <a href="#">{{ __('navigation.about') }}</a>
        <a href="#">{{ __('navigation.contact') }}</a>
    </nav>
</header>

@yield('content')

<x-frontend.footer />

<script src="{{ asset('assets/js/dropdown.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
</body>
</html>
