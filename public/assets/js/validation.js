/**
 * Global Validation Popover Controller — hover on desktop, click/tap on
 * mobile & keyboard. Works for both server-rendered errors (page load) and
 * XHR 422 responses that add .has-error + a popover to a field afterward.
 */
(function () {
  function closeAll(except) {
    document.querySelectorAll('.vfield.popover-open').forEach(function (f) {
      if (f !== except) { f.classList.remove('popover-open'); f.dataset.pinned = ''; }
    });
  }

  function initField(field) {
    var btn = field.querySelector('.vfield-icon');
    if (!btn || btn.dataset.validationInit === 'true') return;
    btn.dataset.validationInit = 'true';

    btn.addEventListener('mouseenter', function () {
      if (field.classList.contains('has-error')) field.classList.add('popover-open');
    });
    btn.addEventListener('mouseleave', function () {
      if (field.dataset.pinned !== 'true') field.classList.remove('popover-open');
    });
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (!field.classList.contains('has-error')) return;
      var wasPinned = field.dataset.pinned === 'true';
      closeAll(field);
      if (wasPinned) {
        field.classList.remove('popover-open');
        field.dataset.pinned = '';
      } else {
        field.classList.add('popover-open');
        field.dataset.pinned = 'true';
      }
    });
    btn.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        field.classList.remove('popover-open');
        field.dataset.pinned = '';
        btn.blur();
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.vfield').forEach(initField);
    document.addEventListener('click', function () { closeAll(); });
  });

  window.GlobalValidation = { initField: initField, closeAll: closeAll };
})();
