<div class="modal fade"
     id="deleteConfirmModal"
     tabindex="-1"
     aria-labelledby="deleteConfirmModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">
                    <?= $deleteTitle ?? 'Delete Data'; ?>
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body text-center">
                <i class="ri-delete-bin-6-line text-danger fs-1 mb-3"></i>
                <p class="mb-2">
                    <?= $deleteMessage ?? 'Are you sure you want to delete this data?'; ?>
                </p>
                <small class="text-muted">
                    This action cannot be undone.
                </small>
            </div>

            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <a href=""
                   id="confirmDeleteButton"
                   class="btn btn-danger">

                    <i class="ri-delete-bin-line"></i>
                    Delete
                </a>
            </div>
        </div>
    </div>
</div>