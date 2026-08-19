/**
 * User "View" page — delete action only (view itself is server-rendered Blade).
 * Reuses the Global Confirmation Modal + Toast + XHR pattern.
 * On success, navigates back to the users index since this record's page
 * no longer exists (this is normal navigation, not a location.reload()).
 */
document.addEventListener('DOMContentLoaded', function () {
  var btn = document.getElementById('viewUserDeleteBtn');
  if (!btn || !window.usersRoutes) return;

  btn.addEventListener('click', function () {
    var labels = window.usersLabels || {};
    var status = btn.dataset.status;
    var statusLabel = status === 'active' ? labels.active : labels.inactive;

    window.ConfirmationModal.open({
      type: 'delete',
      title: labels.confirmDeleteTitle,
      message: labels.confirmDeleteMessage,
      item: {
        name: btn.dataset.name,
        fields: [
          { label: labels.idLabel || 'ID', value: '#' + btn.dataset.id },
          { label: 'Email', value: btn.dataset.email },
          { label: labels.columnStatus, value: statusLabel, badge: true, badgeType: status }
        ]
      },
      confirmText: labels.delete,
      cancelText: labels.cancel,
      onConfirm: function () {
        return new Promise(function (resolve, reject) {
          var xhr = new XMLHttpRequest();
          xhr.open('DELETE', usersRoutes.destroy, true);
          xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
          var csrfMeta = document.querySelector('meta[name="csrf-token"]');
          if (csrfMeta) xhr.setRequestHeader('X-CSRF-TOKEN', csrfMeta.getAttribute('content'));

          xhr.onreadystatechange = function () {
            if (xhr.readyState !== XMLHttpRequest.DONE) return;
            if (xhr.status >= 200 && xhr.status < 300) {
              resolve();
              window.location.href = usersRoutes.index;
            } else {
              if (window.Toast) Toast.error(labels.deleteError || 'Unable to delete.');
              reject(new Error('delete failed'));
            }
          };
          xhr.send();
        });
      }
    });
  });
});