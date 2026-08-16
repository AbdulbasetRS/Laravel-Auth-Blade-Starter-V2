{{-- Reusable calendar date-picker partial. $filterKey ties it to the JS filters object. --}}
<div class="date-field" data-role="date-picker" data-filter="{{ $filterKey }}">
    <button type="button" class="date-field-trigger" aria-haspopup="true" aria-expanded="false">
        <span class="date-value" data-placeholder="{{ __('users.choose_date') }}">{{ __('users.choose_date') }}</span>
        <x-icon name="calendar" />
    </button>
    <div class="date-field-popover" role="dialog">
        <div class="cal-header">
            <button type="button" class="cal-nav-btn" data-nav="prev">‹</button>
            <span class="cal-month-label"></span>
            <button type="button" class="cal-nav-btn" data-nav="next">›</button>
        </div>
        <div class="cal-weekdays">
            <span>{{ __('users.weekday_sun') }}</span><span>{{ __('users.weekday_mon') }}</span><span>{{ __('users.weekday_tue') }}</span>
            <span>{{ __('users.weekday_wed') }}</span><span>{{ __('users.weekday_thu') }}</span><span>{{ __('users.weekday_fri') }}</span><span>{{ __('users.weekday_sat') }}</span>
        </div>
        <div class="cal-days"></div>
        <div class="cal-footer"><button type="button" class="cal-clear-btn">{{ __('users.clear_date') }}</button></div>
    </div>
</div>
