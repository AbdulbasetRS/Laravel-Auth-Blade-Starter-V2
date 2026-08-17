/**
 * Global Dropdown Controller — the single controller behind every dropdown
 * in the project (language switcher, user menu, filters, row actions,
 * export menu, column visibility). Exposed on window.GlobalDropdown so
 * pages that inject rows dynamically (e.g. the Users table) can initialize
 * new dropdown instances after rendering.
 */
(function () {
  function closeAll(except) {
    document.querySelectorAll('.gdropdown.open').forEach(function (dd) {
      if (dd !== except) close(dd);
    });
  }

  function close(dd) {
    dd.classList.remove('open');
    var trigger = dd.querySelector('.gdropdown-trigger');
    if (trigger) trigger.setAttribute('aria-expanded', 'false');

    // Keep the open-up positioning until the fade-out transition finishes,
    // so the menu never snaps from "above" to "below" mid-close.
    if (dd.classList.contains('open-up')) {
      var menu = dd.querySelector('.gdropdown-menu');
      var cleanup = function (e) {
        if (e && e.target !== menu) return;
        dd.classList.remove('open-up');
        menu.removeEventListener('transitionend', cleanup);
      };
      menu.addEventListener('transitionend', cleanup);
      setTimeout(cleanup, 200); // fallback safety net
    }
  }

  function position(root) {
    var menu = root.querySelector('.gdropdown-menu');
    var triggerRect = root.getBoundingClientRect();
    var menuHeight = menu.offsetHeight;
    var margin = 16;
    var spaceBelow = window.innerHeight - triggerRect.bottom;
    var spaceAbove = triggerRect.top;
    var needsFlip = spaceBelow < (menuHeight + margin) && spaceAbove > spaceBelow;
    root.classList.toggle('open-up', needsFlip);
  }

  function init(root) {
    if (root.dataset.dropdownInit === 'true') return;
    root.dataset.dropdownInit = 'true';

    var trigger = root.querySelector('.gdropdown-trigger');
    var menu = root.querySelector('.gdropdown-menu');
    if (!trigger || !menu) return;

    trigger.addEventListener('click', function (e) {
      e.stopPropagation();
      var willOpen = !root.classList.contains('open');
      closeAll();
      if (willOpen) position(root);
      root.classList.toggle('open', willOpen);
      trigger.setAttribute('aria-expanded', String(willOpen));
    });

    root.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        root.classList.remove('open', 'open-up');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.focus();
      }
    });

    // Clicks inside the menu never bubble to the document "close all"
    // listener — each item type below decides for itself whether to close.
    menu.addEventListener('click', function (e) { e.stopPropagation(); });

    if (root.classList.contains('select-dropdown')) {
      var valueLabel = trigger.querySelector('.select-value');

      root.querySelectorAll('.gdropdown-item:not(.custom-number-item)').forEach(function (item) {
        item.addEventListener('click', function () {
          root.querySelectorAll('.gdropdown-item').forEach(function (i) { i.classList.remove('selected'); });
          item.classList.add('selected');
          if (valueLabel) valueLabel.textContent = item.textContent.trim();
          root.dispatchEvent(new CustomEvent('select-change', { detail: { value: item.dataset.value, label: item.textContent.trim() } }));
          closeAll();
        });
      });

      var customInput = root.querySelector('.page-size-custom-input');
      if (customInput) {
        var customItem = customInput.closest('.gdropdown-item');
        function commitCustomValue() {
          var val = customInput.value.trim();
          if (!val) return;
          root.querySelectorAll('.gdropdown-item').forEach(function (i) { i.classList.remove('selected'); });
          customItem.classList.add('selected');
          if (valueLabel) valueLabel.textContent = val;
          root.dispatchEvent(new CustomEvent('select-change', { detail: { value: val, label: val } }));
          closeAll();
        }
        customInput.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); commitCustomValue(); }
        });
        customInput.addEventListener('click', function (e) { e.stopPropagation(); });
      }
    } else {
      // Plain action items (row actions, export formats, user menu) close after choosing.
      // Checkbox items (multi-select columns) and real navigation links stay open / navigate normally.
      root.querySelectorAll('.gdropdown-item:not(.checkbox-item)').forEach(function (item) {
        if (item.tagName === 'A') return; // real links navigate; nothing to intercept
        item.addEventListener('click', function () { closeAll(); });
      });
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.gdropdown').forEach(init);
    document.addEventListener('click', function () { closeAll(); });
  });

  window.GlobalDropdown = { init: init, closeAll: closeAll, position: position };
})();
