{{--
    Global Confirmation Modal — the ONLY confirmation UI used anywhere in the
    project (delete, approve, reject, archive...). Content is fully dynamic,
    populated by window.ConfirmationModal.open({...}) — see confirmation-modal.js.
    Never use browser confirm()/alert() instead of this.
--}}
<div class="confirm-modal-overlay" id="confirmModalOverlay">
    <div class="confirm-modal" id="confirmModal" role="alertdialog" aria-modal="true"
         aria-labelledby="confirmModalTitle" aria-describedby="confirmModalMessage" tabindex="-1">
        <div class="confirm-modal-header">
            <div class="confirm-modal-icon" id="confirmModalIcon"></div>
            <div class="confirm-modal-heading">
                <h2 class="confirm-modal-title" id="confirmModalTitle"></h2>
            </div>
            <button type="button" class="confirm-modal-close" id="confirmModalClose" aria-label="{{ __('common.close') }}">
                <x-icon name="x" />
            </button>
        </div>
        <div class="confirm-modal-body">
            <p class="confirm-modal-message" id="confirmModalMessage"></p>
            <div class="confirm-item-card" id="confirmModalItem"></div>
        </div>
        <div class="confirm-modal-footer">
            <button type="button" class="btn-ghost" id="confirmModalCancel" data-default-label="{{ __('common.cancel') }}"></button>
            <button type="button" class="btn btn-primary" id="confirmModalConfirm">
                <span class="spinner" id="confirmModalSpinner"></span>
                <span id="confirmModalConfirmLabel" data-default-label="{{ __('common.confirm') }}"></span>
            </button>
        </div>
    </div>
</div>