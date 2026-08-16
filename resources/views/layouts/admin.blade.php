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

<script src="{{ asset('assets/js/dropdown.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/admin.js') }}"></script>

<script>
  /**
   * Documentation page — in-page nav scrolling.
   *
   * Why this exists: the actual scrollable element in the Admin layout is
   * .admin-main (not <html>/<body> — see admin.css: .admin-body and
   * .admin-content-col are overflow:hidden). Native <a href="#id"> anchor
   * jumps don't reliably resolve against a nested overflow:auto ancestor
   * across browsers, which was causing the page to snap back to the top
   * instead of scrolling to the clicked section. This file replaces the
   * native jump with an explicit, contained smooth-scroll + active-link
   * highlighting via IntersectionObserver.
   */
  document.addEventListener('DOMContentLoaded', function () {
    var nav = document.querySelector('.docs-nav');
    var scrollContainer = document.querySelector('.admin-main');
    if (!nav || !scrollContainer) return;

    var links = Array.prototype.slice.call(nav.querySelectorAll('a[href^="#"]'));
    var sections = links
      .map(function (link) {
        var id = link.getAttribute('href').slice(1);
        return document.getElementById(id);
      })
      .filter(Boolean);

    function setActive(id) {
      links.forEach(function (link) {
        link.classList.toggle('active', link.getAttribute('href') === '#' + id);
      });
    }

    links.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var id = link.getAttribute('href').slice(1);
        var target = document.getElementById(id);
        if (!target) return;

        e.preventDefault();

        // Scroll within the real scroll container, not the window.
        var containerTop = scrollContainer.getBoundingClientRect().top;
        var targetTop = target.getBoundingClientRect().top;
        var offset = targetTop - containerTop + scrollContainer.scrollTop;

        scrollContainer.scrollTo({ top: offset, behavior: 'smooth' });

        // Keep the URL shareable without triggering a native jump.
        history.replaceState(null, '', '#' + id);
        setActive(id);
      });
    });

    // Highlight the section currently in view while the user scrolls manually.
    if ('IntersectionObserver' in window && sections.length) {
      var observer = new IntersectionObserver(
        function (entries) {
          entries.forEach(function (entry) {
            if (entry.isIntersecting) setActive(entry.target.id);
          });
        },
        { root: scrollContainer, rootMargin: '-10% 0px -70% 0px', threshold: 0 }
      );
      sections.forEach(function (section) { observer.observe(section); });
    }
  });
</script>
@stack('scripts')
</body>
</html>
