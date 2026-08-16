/**
 * Admin layout behavior: sidebar toggle (desktop collapse / mobile drawer),
 * expandable sidebar groups, and the client-side live clock.
 */
document.addEventListener('DOMContentLoaded', function () {
  var sidebar = document.getElementById('adminSidebar');
  var sidebarToggle = document.getElementById('sidebarToggle');
  var sidebarOverlay = document.getElementById('sidebarOverlay');
  function isMobile() { return window.innerWidth <= 860; }

  if (sidebarToggle && sidebar && sidebarOverlay) {
    sidebarToggle.addEventListener('click', function () {
      if (isMobile()) {
        var open = sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('show', open);
      } else {
        sidebar.classList.toggle('collapsed');
      }
    });
    sidebarOverlay.addEventListener('click', function () {
      sidebar.classList.remove('mobile-open');
      sidebarOverlay.classList.remove('show');
    });
    window.addEventListener('resize', function () {
      if (!isMobile()) {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('show');
      }
    });
  }

  document.querySelectorAll('.sidebar-group-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (sidebar && sidebar.classList.contains('collapsed') && !isMobile()) return;
      var group = btn.closest('.sidebar-group');
      var open = group.classList.toggle('open');
      btn.setAttribute('aria-expanded', String(open));
    });
  });

  // ---------- Live clock (client-side only, no server polling) ----------
  var clockEl = document.getElementById('navbarClock');
  if (clockEl) {
    var timezone = clockEl.getAttribute('data-timezone') || 'UTC';
    var lang = document.documentElement.getAttribute('lang') || 'en';

    function updateClock() {
      var now = new Date();
      var baseLocale = lang === 'ar' ? 'ar-EG' : 'en-US';
      var locale = baseLocale + '-u-nu-latn';
      var timeFmt = new Intl.DateTimeFormat(locale, { timeZone: timezone, hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
      var dateFmt = new Intl.DateTimeFormat(locale, { timeZone: timezone, weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
      document.getElementById('clockTime').textContent = timeFmt.format(now);
      document.getElementById('clockTz').textContent = '• ' + timezone;
      document.getElementById('clockDate').textContent = dateFmt.format(now);
    }
    updateClock();
    setInterval(updateClock, 1000);
  }

  // ---------- Filters accordion (used on Users table and future filtered pages) ----------
  var filtersToggleBtn = document.getElementById('filtersToggleBtn');
  var filtersAccordion = document.getElementById('filtersAccordion');
  if (filtersToggleBtn && filtersAccordion) {
    filtersToggleBtn.addEventListener('click', function () {
      var opening = !filtersAccordion.classList.contains('open');
      filtersToggleBtn.classList.toggle('open', opening);
      filtersToggleBtn.setAttribute('aria-expanded', String(opening));

      if (opening) {
        filtersAccordion.classList.add('open');
        filtersAccordion.classList.remove('overflow-visible');
        filtersAccordion.style.maxHeight = filtersAccordion.scrollHeight + 'px';
        filtersAccordion.addEventListener('transitionend', function onEnd(e) {
          if (e.propertyName === 'max-height') {
            filtersAccordion.classList.add('overflow-visible');
            filtersAccordion.removeEventListener('transitionend', onEnd);
          }
        });
      } else {
        filtersAccordion.style.maxHeight = filtersAccordion.scrollHeight + 'px';
        filtersAccordion.classList.remove('overflow-visible');
        void filtersAccordion.offsetHeight; // force reflow
        filtersAccordion.style.maxHeight = '0px';
        filtersAccordion.classList.remove('open');
        if (window.GlobalDropdown) window.GlobalDropdown.closeAll();
      }
    });

    window.addEventListener('resize', function () {
      if (filtersAccordion.classList.contains('open')) {
        filtersAccordion.style.maxHeight = filtersAccordion.scrollHeight + 'px';
      }
    });
  }

  // ---------- Custom date picker (calendar dialog, replaces free-text date input) ----------
  function positionCalendar(root) {
    var menu = root.querySelector('.date-field-popover');
    var triggerRect = root.getBoundingClientRect();
    var menuHeight = menu.offsetHeight;
    var margin = 16;
    var spaceBelow = window.innerHeight - triggerRect.bottom;
    var spaceAbove = triggerRect.top;
    var needsFlip = spaceBelow < (menuHeight + margin) && spaceAbove > spaceBelow;
    root.classList.toggle('open-up', needsFlip);
  }
  function closeAllDatePickers(except) {
    document.querySelectorAll('.date-field.open').forEach(function (f) {
      if (f !== except) {
        f.classList.remove('open');
        f.querySelector('.date-field-trigger').setAttribute('aria-expanded', 'false');
      }
    });
  }
  function initDatePicker(root) {
    var current = new Date();
    var selected = null;
    var trigger = root.querySelector('.date-field-trigger');
    var valueEl = root.querySelector('.date-value');
    var monthLabel = root.querySelector('.cal-month-label');
    var daysEl = root.querySelector('.cal-days');
    var popover = root.querySelector('.date-field-popover');
    var lang = document.documentElement.getAttribute('lang') || 'ar';
    var locale = (lang === 'ar' ? 'ar-EG' : 'en-US') + '-u-nu-latn';

    function render() {
      monthLabel.textContent = current.toLocaleDateString(locale, { month: 'long', year: 'numeric' });
      var year = current.getFullYear(), month = current.getMonth();
      var startWeekday = new Date(year, month, 1).getDay();
      var daysInMonth = new Date(year, month + 1, 0).getDate();
      var html = '';
      for (var i = 0; i < startWeekday; i++) html += '<span class="cal-day empty"></span>';
      for (var d = 1; d <= daysInMonth; d++) {
        var isSelected = selected && selected.getFullYear() === year && selected.getMonth() === month && selected.getDate() === d;
        html += '<button type="button" class="cal-day' + (isSelected ? ' selected' : '') + '" data-day="' + d + '">' + d + '</button>';
      }
      daysEl.innerHTML = html;
    }
    render();

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var willOpen = !root.classList.contains('open');
      closeAllDatePickers(root);
      if (window.GlobalDropdown) window.GlobalDropdown.closeAll();
      if (willOpen) positionCalendar(root);
      root.classList.toggle('open', willOpen);
      trigger.setAttribute('aria-expanded', String(willOpen));
    });
    popover.addEventListener('click', function (e) { e.stopPropagation(); });

    root.querySelector('.cal-header').addEventListener('click', function (e) {
      var btn = e.target.closest('.cal-nav-btn');
      if (!btn) return;
      current.setMonth(current.getMonth() + (btn.dataset.nav === 'next' ? 1 : -1));
      render();
    });

    daysEl.addEventListener('click', function (e) {
      var btn = e.target.closest('.cal-day:not(.empty)');
      if (!btn) return;
      selected = new Date(current.getFullYear(), current.getMonth(), parseInt(btn.dataset.day, 10));
      var y = selected.getFullYear();
      var m = String(selected.getMonth() + 1).padStart(2, '0');
      var d = String(selected.getDate()).padStart(2, '0');
      var iso = y + '-' + m + '-' + d;
      valueEl.textContent = iso;
      valueEl.classList.add('has-value');
      root.dispatchEvent(new CustomEvent('date-change', { detail: { value: iso } }));
      render();
      root.classList.remove('open');
      trigger.setAttribute('aria-expanded', 'false');
    });

    var clearBtn = root.querySelector('.cal-clear-btn');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        selected = null;
        valueEl.textContent = valueEl.dataset.placeholder || valueEl.textContent;
        valueEl.classList.remove('has-value');
        root.dispatchEvent(new CustomEvent('date-change', { detail: { value: '' } }));
        render();
        root.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
      });
    }

    root.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        root.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.focus();
      }
    });
  }
  document.querySelectorAll('[data-role="date-picker"]').forEach(initDatePicker);
  document.addEventListener('click', function () { closeAllDatePickers(); });

  // ---------- Column visibility toggle (multi-select dropdown) ----------
  document.querySelectorAll('[data-role="columns"] input[type="checkbox"]').forEach(function (cb) {
    cb.addEventListener('change', function () {
      var col = cb.dataset.column;
      document.querySelectorAll('[data-col="' + col + '"]').forEach(function (el) {
        el.style.display = cb.checked ? '' : 'none';
      });
    });
  });
});
