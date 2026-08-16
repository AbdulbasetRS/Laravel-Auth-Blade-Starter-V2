<div class="admin-footer">
    <span>{{ __('common.copyright', ['year' => date('Y'), 'app' => config('app.name')]) }}</span>
    <div class="footer-lang-slot">
        <span class="footer-lang-label">{{ __('common.language') }}</span>
        <div class="footer-controls">
            <x-language-switcher />
            <x-theme-toggle />
        </div>
    </div>
</div>
