<div 
    class="modal fade" 
    id="addUserModal" 
    tabindex="-1" 
    aria-labelledby="addUserModalLabel" 
    aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <!-- Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">
                    Add New User
                </h5>
                <button 
                    type="button" 
                    class="btn-close" 
                    data-bs-dismiss="modal"
                    aria-label="Close">
                </button>
            </div>

            <!-- Form -->
            <form 
                action="index.php?page=users&action=store" 
                method="POST"
                enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Name -->
                    <div class="mb-3">
                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter user name"
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
                            placeholder="Enter email address"
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
                            placeholder="Enter password"
                            required>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label">
                            Role
                        </label>

                        <select 
                            name="role"
                            class="form-select">

                            <option value="User">
                                User
                            </option>

                            <option value="Admin">
                                Admin
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

                            <option value="Active">
                                Active
                            </option>

                            <option value="Inactive">
                                Inactive
                            </option>
                        </select>
                    </div>

                    <!-- Avatar -->
                        <div class="mb-3">
                            <label class="form-label">
                                Profile Photo
                            </label>
                            
                            <input
                                type="file"
                                name="avatar"
                                class="form-control"
                                accept="image/*">

                            <small class="text-muted">
                                JPG, PNG, JPEG (Max 2MB)
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
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>