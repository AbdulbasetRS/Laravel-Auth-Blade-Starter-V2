{{-- Live client-side clock — no polling, no server requests every second.
     Timezone comes from config('app.timezone'), never hardcoded in JS. --}}
<div class="navbar-clock" id="navbarClock" role="timer" aria-live="off" data-timezone="{{ config('app.timezone') }}">
    <x-icon name="clock" class="clock-icon" />
    <div class="clock-lines">
        <div class="clock-time"><span id="clockTime">--:--:--</span><span class="tz" id="clockTz"></span></div>
        <div class="clock-date" id="clockDate"></div>
    </div>
</div>
