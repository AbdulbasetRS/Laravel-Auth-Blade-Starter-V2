/**
 * Users table — Data Request via XMLHttpRequest only (never fetch/axios/jQuery),
 * per the project's XHR rule. Page itself is Blade; this file only owns the
 * table body / pagination / result count / table states / export.
 */
document.addEventListener('DOMContentLoaded', function () {
  var tbody = document.getElementById('usersTableBody');
  var pagination = document.getElementById('usersPagination');
  var resultCount = document.getElementById('resultCount');
  if (!tbody || !window.usersRoutes) return;

  var filters = {
    search: '', status: 'all', verified: '', date_from: '', date_to: '',
    sort: 'newest', per_page: 10, page: 1,
  };

  var searchDebounce = null;
  var searchInput = document.querySelector('.filters-bar .search-input input');
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      searchDebounce = setTimeout(function () {
        filters.search = searchInput.value.trim();
        filters.page = 1;
        fetchUsers();
      }, 350);
    });
  }

  document.querySelectorAll('.select-dropdown[data-filter]').forEach(function (dd) {
    dd.addEventListener('select-change', function (e) {
      filters[dd.dataset.filter] = e.detail.value;
    });
  });

  document.querySelectorAll('[data-role="date-picker"][data-filter]').forEach(function (dd) {
    dd.addEventListener('date-change', function (e) {
      filters[dd.dataset.filter] = e.detail.value;
    });
  });

  var applyBtn = document.getElementById('applyFiltersBtn');
  if (applyBtn) {
    applyBtn.addEventListener('click', function () {
      filters.page = 1;
      fetchUsers();
    });
  }

  var resetBtn = document.getElementById('resetFiltersBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      filters = { search: '', status: 'all', verified: '', date_from: '', date_to: '', sort: 'newest', per_page: 10, page: 1 };
      if (searchInput) searchInput.value = '';
      document.querySelectorAll('.select-dropdown').forEach(function (dd) {
        var items = dd.querySelectorAll('.gdropdown-item');
        items.forEach(function (i, idx) { i.classList.toggle('selected', idx === 0); });
        var valueLabel = dd.querySelector('.select-value');
        if (valueLabel && items.length) valueLabel.textContent = items[0].textContent.trim();
      });
      document.querySelectorAll('.date-value').forEach(function (el) {
        el.textContent = el.dataset.placeholder || el.textContent;
        el.classList.remove('has-value');
      });
      fetchUsers();
    });
  }

  function escapeHtml(str) {
    var div = document.createElement('div');
    div.textContent = str == null ? '' : String(str);
    return div.innerHTML;
  }

  function renderRow(user) {
    var initial = escapeHtml((user.name || '?').charAt(0));
    var statusBadge = user.status === 'active'
      ? '<span class="badge active"><span class="badge-dot"></span>Active</span>'
      : '<span class="badge inactive"><span class="badge-dot"></span>Inactive</span>';
    var verified = user.email_verified_at
      ? '<span class="verified-yes"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>' + (window.usersLabels ? usersLabels.yes : 'Yes') + '</span>'
      : '<span class="verified-no"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6L6 18M6 6l12 12"/></svg>' + (window.usersLabels ? usersLabels.no : 'No') + '</span>';

    return '' +
      '<tr>' +
        '<td data-col="user"><div class="user-cell">' +
          '<div class="user-avatar-sm">' + initial + '</div>' +
          '<div><div class="user-cell-name">' + escapeHtml(user.name) + '</div>' +
          '<div class="user-cell-email">' + escapeHtml(user.email) + '</div></div>' +
        '</div></td>' +
        '<td data-col="status">' + statusBadge + '</td>' +
        '<td data-col="verified">' + verified + '</td>' +
        '<td data-col="joined" style="font-family:\'IBM Plex Mono\',monospace;font-size:12px;">' + escapeHtml((user.created_at || '').substring(0, 10)) + '</td>' +
        '<td data-col="actions">' +
          '<div class="gdropdown" data-role="row-actions">' +
            '<button class="page-btn gdropdown-trigger" type="button" style="width:32px;padding:0;" aria-haspopup="true" aria-expanded="false">' +
              '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>' +
            '</button>' +
            '<div class="gdropdown-menu" role="menu">' +
              '<div class="gdropdown-item" role="menuitem">' + (window.usersLabels ? usersLabels.edit : 'Edit') + '</div>' +
              '<div class="gdropdown-item" role="menuitem">' + (window.usersLabels ? usersLabels.view : 'View') + '</div>' +
              '<div class="gdropdown-item destructive" role="menuitem" data-action="delete-user" ' +
                'data-id="' + escapeHtml(user.id) + '" data-name="' + escapeHtml(user.name) + '" ' +
                'data-email="' + escapeHtml(user.email) + '" data-status="' + escapeHtml(user.status) + '">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg> ' +
                (window.usersLabels ? usersLabels.delete : 'Delete') +
              '</div>' +
            '</div>' +
          '</div>' +
        '</td>' +
      '</tr>';
  }

  function renderPagination(meta) {
    if (!pagination) return;
    var html = '';
    html += '<button class="page-btn" data-page="' + (meta.current_page - 1) + '" ' + (meta.current_page <= 1 ? 'disabled' : '') + '>‹</button>';
    for (var p = 1; p <= meta.last_page; p++) {
      html += '<button class="page-btn' + (p === meta.current_page ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
    }
    html += '<button class="page-btn" data-page="' + (meta.current_page + 1) + '" ' + (meta.current_page >= meta.last_page ? 'disabled' : '') + '>›</button>';
    pagination.innerHTML = html;

    pagination.querySelectorAll('.page-btn:not([disabled])').forEach(function (btn) {
      btn.addEventListener('click', function () {
        filters.page = parseInt(btn.dataset.page, 10);
        fetchUsers();
      });
    });
  }

  function showState(kind) {
    var messages = window.usersLabels || {};
    if (kind === 'loading') {
      tbody.innerHTML = '<tr class="table-state-row"><td colspan="5">' + (messages.loading || 'Loading...') + '</td></tr>';
    } else if (kind === 'empty') {
      tbody.innerHTML = '<tr class="table-state-row"><td colspan="5">' + (messages.empty || 'No records found.') + '</td></tr>';
    } else if (kind === 'error') {
      tbody.innerHTML = '<tr class="table-state-row"><td colspan="5">' +
        (messages.error || 'Unable to load data.') +
        ' <button type="button" class="btn-ghost btn-sm" id="retryUsersBtn">' + (messages.retry || 'Retry') + '</button></td></tr>';
      var retryBtn = document.getElementById('retryUsersBtn');
      if (retryBtn) retryBtn.addEventListener('click', fetchUsers);
    }
  }

  function fetchUsers() {
    showState('loading');

    var params = Object.keys(filters).map(function (key) {
      return encodeURIComponent(key) + '=' + encodeURIComponent(filters[key]);
    }).join('&');

    var xhr = new XMLHttpRequest();
    xhr.open('GET', usersRoutes.data + '?' + params, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

    xhr.onreadystatechange = function () {
      if (xhr.readyState !== XMLHttpRequest.DONE) return;

      if (xhr.status >= 200 && xhr.status < 300) {
        try {
          var response = JSON.parse(xhr.responseText);
        } catch (err) {
          showState('error');
          return;
        }
        if (!response.success || !response.data || response.data.length === 0) {
          showState('empty');
          if (resultCount) resultCount.textContent = '';
          if (pagination) pagination.innerHTML = '';
          return;
        }

        tbody.innerHTML = response.data.map(renderRow).join('');
        tbody.querySelectorAll('.gdropdown').forEach(function (dd) { window.GlobalDropdown.init(dd); });
        bindDeleteButtons();
        if (resultCount && response.meta) {
          var tpl = (window.usersLabels && usersLabels.showingCount) || 'Showing :from–:to of :total';
          resultCount.textContent = tpl
            .replace(':from', response.meta.from)
            .replace(':to', response.meta.to)
            .replace(':total', response.meta.total);
        }
        renderPagination(response.meta);
      } else {
        showState('error');
      }
    };

    xhr.send();
  }

  // ---------- Export (Excel / CSV) — respects the current active filters ----------
  var EXPORT_FILTER_KEYS = ['search', 'status', 'verified', 'date_from', 'date_to', 'sort'];

  document.querySelectorAll('[data-export]').forEach(function (link) {
    link.addEventListener('click', function (e) {
      e.preventDefault();

      var format = link.dataset.export;
      var baseUrl = format === 'excel' ? usersRoutes.exportExcel : usersRoutes.exportCsv;

      var params = EXPORT_FILTER_KEYS
        .filter(function (key) { return filters[key] !== '' && filters[key] != null; })
        .map(function (key) { return encodeURIComponent(key) + '=' + encodeURIComponent(filters[key]); })
        .join('&');

      window.location.href = params ? baseUrl + '?' + params : baseUrl;

      if (window.GlobalDropdown) window.GlobalDropdown.closeAll();
    });
  });

    // ---------- Delete (Confirmation Modal → XHR DELETE → refresh table) ----------
  function deleteUser(id) {
    return new Promise(function (resolve, reject) {
      var url = usersRoutes.destroy.replace('__ID__', id);
      var xhr = new XMLHttpRequest();
      xhr.open('DELETE', url, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      var csrfMeta = document.querySelector('meta[name="csrf-token"]');
      if (csrfMeta) xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.getAttribute('content'));

      xhr.onreadystatechange = function () {
        if (xhr.readyState !== XMLHttpRequest.DONE) return;
        if (xhr.status >= 200 && xhr.status < 300) {
          if (window.Toast) Toast.success((window.usersLabels && usersLabels.deleteSuccess) || 'Deleted successfully.');
          fetchUsers();
          resolve();
        } else {
          if (window.Toast) Toast.error((window.usersLabels && usersLabels.deleteError) || 'Unable to delete.');
          reject(new Error('delete failed'));
        }
      };
      xhr.send();
    });
  }

 function bindDeleteButtons() {
  tbody.querySelectorAll('[data-action="delete-user"]').forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      if (window.GlobalDropdown) window.GlobalDropdown.closeAll();

      var labels = window.usersLabels || {};
      var status = trigger.dataset.status;
      var statusLabel = status === 'active' ? labels.active : labels.inactive;

      window.ConfirmationModal.open({
        type: 'delete',
        title: labels.confirmDeleteTitle,
        message: labels.confirmDeleteMessage,
        item: {
          name: trigger.dataset.name,
          fields: [
            { label: labels.idLabel || 'ID', value: '#' + trigger.dataset.id },
            { label: 'Email', value: trigger.dataset.email },
            { label: labels.columnStatus, value: statusLabel, badge: true, badgeType: status }
          ]
        },
        confirmText: labels.delete,
        cancelText: labels.cancel,
        onConfirm: function () {
          return deleteUser(trigger.dataset.id);
        }
      });
    });
  });
}

  fetchUsers();
});