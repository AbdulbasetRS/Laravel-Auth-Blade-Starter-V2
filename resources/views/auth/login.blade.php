<!DOCTYPE html>
<html lang="{{ LaravelLocalization::getCurrentLocale() }}"
      dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}"
      data-theme="light" id="htmlRoot">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('auth.login') }} — {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700;800&family=Cairo:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/design-system.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">
<script>
  (function () {
    var m = document.cookie.match(/(?:^|; )theme=(dark|light)/);
    if (m && m[1] === 'dark') { document.documentElement.setAttribute('data-theme', 'dark'); }
  })();
</script>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand-panel">
        <div class="login-brand-top"><span class="dot"></span> {{ config('app.name') }}</div>
        <div class="login-brand-mid">
            <h2>{{ __('auth.brand_headline') }}</h2>
            <p>{{ __('auth.brand_body') }}</p>
        </div>
        <div class="login-brand-bottom">{{ __('common.copyright', ['year' => date('Y'), 'app' => config('app.name')]) }}</div>
    </div>

    <div class="login-form-panel">
        <div class="login-card">
            <p class="eyebrow">{{ __('auth.login') }}</p>
            <h1>{{ __('auth.welcome_back') }}</h1>
            <p class="sub">{{ __('auth.welcome_subtitle') }}</p>

            @if ($errors->has('credentials'))
                <div class="login-alert show">
                    <x-icon name="alert-circle" />
                    <span>{{ $errors->first('credentials') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" id="loginForm" novalidate>
                @csrf

                <x-form.field name="email" type="email" :label="__('auth.email')" placeholder="example@domain.com" required />
                <x-form.field name="password" type="password" :label="__('auth.password')" :toggle="true" required />

                <div class="remember-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember"> {{ __('auth.remember') }}
                    </label>
                    <a href="#" class="forgot-link">{{ __('auth.forgot_password') }}</a>
                </div>

                <button type="submit" class="btn btn-primary btn-block" id="loginBtn">
                    <span class="spinner"></span>
                    <span class="btn-label">{{ __('auth.login') }}</span>
                </button>
            </form>

            <div class="login-footer-slot">
                <span class="footer-lang-label">{{ __('common.language') }}</span>
                <div class="footer-controls">
                    <x-language-switcher />
                    <x-theme-toggle />
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/dropdown.js') }}"></script>
<script src="{{ asset('assets/js/validation.js') }}"></script>
<script src="{{ asset('assets/js/theme.js') }}"></script>
<script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
