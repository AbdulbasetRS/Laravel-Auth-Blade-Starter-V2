/**
 * Global Confirmation Modal Controller.
 * Usage:
 *   ConfirmationModal.open({
 *     type: 'delete',
 *     title, message,
 *     item: { name, fields: [{ label, value, badge, badgeType }] },
 *     confirmText, cancelText,
 *     onConfirm: () => XHR-returning-Promise
 *   });
 * onConfirm must return a Promise; the modal awaits it before closing and
 * shows a loading state on the confirm button meanwhile.
 */
(function () {
  var overlay, modal, iconEl, titleEl, messageEl, itemEl,
      cancelBtn, confirmBtn, confirmLabelEl, closeBtn;
  var activeOptions = null;
  var lastFocusedEl = null;

  var ICONS = {
    'trash': '<path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/>',
    'alert-triangle': '<path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>'
  };

  function renderIcon(name) {
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.innerHTML = ICONS[name] || ICONS['alert-triangle'];
    return svg;
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  function buildItemCard(item) {
    if (!item) return '';
    var html = '<div class="confirm-item-name">' + escapeHtml(item.name || '') + '</div>';
    if (item.fields && item.fields.length) {
      html += '<div class="confirm-item-fields">';
      item.fields.forEach(function (f) {
        var value = f.badge
          ? '<span class="badge ' + (f.badgeType || '') + '"><span class="badge-dot"></span>' + escapeHtml(f.value) + '</span>'
          : escapeHtml(f.value);
        html += '<div class="confirm-item-field"><span class="confirm-item-label">' + escapeHtml(f.label) + '</span><span class="confirm-item-value">' + value + '</span></div>';
      });
      html += '</div>';
    }
    return html;
  }

  function setLoading(loading) {
    confirmBtn.disabled = loading;
    cancelBtn.disabled = loading;
    modal.classList.toggle('is-loading', loading);
  }

  function open(options) {
    activeOptions = options || {};
    lastFocusedEl = document.activeElement;

    var destructive = activeOptions.type === 'delete' || activeOptions.destructive;
    modal.classList.toggle('destructive', !!destructive);

    iconEl.innerHTML = '';
    iconEl.appendChild(renderIcon(activeOptions.icon || (destructive ? 'trash' : 'alert-triangle')));

    titleEl.textContent = activeOptions.title || '';
    messageEl.textContent = activeOptions.message || '';
    itemEl.innerHTML = buildItemCard(activeOptions.item);
    itemEl.style.display = activeOptions.item ? '' : 'none';

    cancelBtn.textContent = activeOptions.cancelText || cancelBtn.dataset.defaultLabel;
    confirmLabelEl.textContent = activeOptions.confirmText || confirmLabelEl.dataset.defaultLabel;

    setLoading(false);
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    modal.focus();
    document.addEventListener('keydown', onKeydown);
  }

  function close() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
    activeOptions = null;
    if (lastFocusedEl && lastFocusedEl.focus) lastFocusedEl.focus();
  }

  function onKeydown(e) {
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'Tab') {
      var focusables = modal.querySelectorAll('button, [href], input, [tabindex]:not([tabindex="-1"])');
      if (!focusables.length) return;
      var first = focusables[0], last = focusables[focusables.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    overlay = document.getElementById('confirmModalOverlay');
    if (!overlay) return;
    modal = document.getElementById('confirmModal');
    iconEl = document.getElementById('confirmModalIcon');
    titleEl = document.getElementById('confirmModalTitle');
    messageEl = document.getElementById('confirmModalMessage');
    itemEl = document.getElementById('confirmModalItem');
    cancelBtn = document.getElementById('confirmModalCancel');
    confirmBtn = document.getElementById('confirmModalConfirm');
    confirmLabelEl = document.getElementById('confirmModalConfirmLabel');
    closeBtn = document.getElementById('confirmModalClose');

    cancelBtn.textContent = cancelBtn.dataset.defaultLabel;
    confirmLabelEl.textContent = confirmLabelEl.dataset.defaultLabel;

    cancelBtn.addEventListener('click', close);
    closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });

    confirmBtn.addEventListener('click', function () {
      if (!activeOptions || typeof activeOptions.onConfirm !== 'function') { close(); return; }
      setLoading(true);
      var result = activeOptions.onConfirm();
      if (result && typeof result.then === 'function') {
        result.then(function () { setLoading(false); close(); })
              .catch(function () { setLoading(false); });
      } else {
        setLoading(false);
        close();
      }
    });
  });

  window.ConfirmationModal = { open: open, close: close };
})();