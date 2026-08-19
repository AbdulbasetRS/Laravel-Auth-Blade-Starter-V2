/**
 * Global Toast System — visually & behaviorally separate from the
 * (future) Notification Center. Client-side only, never persisted.
 */
(function () {
  var TYPE_ICONS = {
    success: '<path d="M20 6L9 17l-5-5"/>',
    error: '<path d="M18 6L6 18M6 6l12 12"/>',
    warning: '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>'
  };

  function renderIcon(name) {
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.innerHTML = TYPE_ICONS[name] || TYPE_ICONS.info;
    return svg;
  }

  function show(message, type, duration) {
    var stack = document.getElementById('toastStack');
    if (!stack) return;
    type = type || 'success';
    duration = duration || 4000;

    var toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.setAttribute('role', 'status');

    var iconWrap = document.createElement('span');
    iconWrap.className = 'toast-icon';
    iconWrap.appendChild(renderIcon(type));

    var msgEl = document.createElement('span');
    msgEl.className = 'toast-message';
    msgEl.textContent = message;

    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'toast-close';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.appendChild(renderIcon('error'));

    toast.appendChild(iconWrap);
    toast.appendChild(msgEl);
    toast.appendChild(closeBtn);
    stack.appendChild(toast);

    requestAnimationFrame(function () { toast.classList.add('show'); });

    var timer = setTimeout(remove, duration);
    function remove() {
      clearTimeout(timer);
      toast.classList.remove('show');
      setTimeout(function () { if (toast.parentNode) toast.remove(); }, 250);
    }
    closeBtn.addEventListener('click', remove);
  }

  window.Toast = {
    success: function (msg, duration) { show(msg, 'success', duration); },
    error: function (msg, duration) { show(msg, 'error', duration); },
    warning: function (msg, duration) { show(msg, 'warning', duration); },
    info: function (msg, duration) { show(msg, 'info', duration); }
  };
})();