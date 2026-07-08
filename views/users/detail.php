<div 
    class="modal fade"
    id="detailUserModal<?= $user['id']; ?>"
    tabindex="-1"
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content user-detail-modal">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title">
                    User Details
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <!-- Body -->
            <div class="modal-body">
                <div class="user-profile-detail">
                    <?php if (!empty($user['avatar'])): ?>
                        <img
                            src="assets/uploads/users/<?= htmlspecialchars($user['avatar']); ?>"
                            class="detail-avatar"
                            alt="<?= htmlspecialchars($user['name']); ?>">
                    <?php else: ?>
                        <div class="detail-avatar-placeholder">
                            <?= userInitials($user['name']); ?>
                        </div>
                    <?php endif; ?>

                    <h4>
                        <?= htmlspecialchars($user['name']); ?>
                    </h4>

                    <p>
                        <?= htmlspecialchars($user['email']); ?>
                    </p>
                </div>

                <div class="user-detail-list">
                    <div class="detail-item">
                        <span>
                            Role
                        </span>
                        <strong>
                            <?= htmlspecialchars($user['role']); ?>
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>
                            Status
                        </span>
                        <strong>
                            <?= htmlspecialchars($user['status']); ?>
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>
                            Join Date
                        </span>
                        <strong>
                            <?= date(
                                'M d, Y',
                                strtotime($user['created_at'])
                            ); ?>
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span>
                            Last Login
                        </span>
                        <strong>
                            <?php if (!empty($user['last_login'])): ?>
                                <?= date(
                                    'M d, Y H:i',
                                    strtotime($user['last_login'])
                                ); ?>
                            <?php else: ?>
                                Never
                            <?php endif; ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-light"
                    data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>