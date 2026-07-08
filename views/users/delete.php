<div 
    class="modal fade"
    id="deleteConfirmModal"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content delete-modal">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?= $deleteTitle ?? 'Delete User'; ?>
                </h5>
                <button 
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body text-center">
                <div class="delete-icon">
                    <i class="ri-delete-bin-line"></i>
                </div>
                <h6 class="mt-3">
                    <?= $deleteMessage ?? 'Are you sure you want to delete this user?'; ?>
                </h6>
                <p class="text-muted mb-0">
                    This action cannot be undone.
                </p>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">
                    Cancel
                </button>

                <a
                    href=""
                    class="btn btn-danger"
                    id="confirmDeleteButton">
                    Delete
                </a>
            </div>
        </div>
    </div>
</div>