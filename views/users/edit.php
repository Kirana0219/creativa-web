<div 
    class="modal fade" 
    id="editUserModal<?= $user['id']; ?>" 
    tabindex="-1" 
    aria-labelledby="editUserModalLabel<?= $user['id']; ?>" 
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel<?= $user['id']; ?>">
                    Edit User
                </h5>
                <button 
                    type="button" 
                    class="btn-close" 
                    data-bs-dismiss="modal">
                </button>
            </div>

            <!-- Form -->
            <form 
                action="index.php?page=users&action=update"
                method="POST"
                enctype="multipart/form-data">

                <div class="modal-body">
                    <input 
                        type="hidden"
                        name="user_id"
                        value="<?= $user['id']; ?>">

                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="<?= htmlspecialchars($user['name']); ?>"
                            required>
                    </div>

                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label">
                            Email
                        </label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= htmlspecialchars($user['email']); ?>"
                            required>
                    </div>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label">
                            Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Leave empty to keep current password">

                        <small class="text-muted">
                            Fill only if you want to change password.
                        </small>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label">
                            Role
                        </label>

                        <select 
                            name="role"
                            class="form-select">

                            <option 
                                value="Admin"
                                <?= $user['role'] === 'Admin' ? 'selected' : ''; ?>>
                                Admin
                            </option>

                            <option 
                                value="User"
                                <?= $user['role'] === 'User' ? 'selected' : ''; ?>>
                                User
                            </option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div class="mb-3">
                        <label class="form-label">
                            Status
                        </label>

                        <select 
                            name="status"
                            class="form-select">

                            <option 
                                value="Active"
                                <?= $user['status'] === 'Active' ? 'selected' : ''; ?>>
                                Active
                            </option>

                            <option 
                                value="Inactive"
                                <?= $user['status'] === 'Inactive' ? 'selected' : ''; ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <!-- Avatar -->
                    <div class="mb-3">
                        <label class="form-label">
                            Profile Photo
                        </label>

                        <?php if (!empty($user['avatar'])): ?>
                            <div class="mb-2">
                                <img 
                                    src="assets/uploads/users/<?= htmlspecialchars($user['avatar']); ?>"
                                    class="user-avatar"
                                    alt="Current Avatar">
                            </div>
                        <?php endif; ?>

                        <input
                            type="file"
                            name="avatar"
                            class="form-control"
                            accept="image/*">
                        <small class="text-muted">
                            Leave empty to keep current photo.
                        </small>
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-light"
                        data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="btn btn-primary">
                        Update User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>