<?php
    include 'views/layout/header.php';
    include 'views/layout/sidebar.php';

    $users = $users ?? [];
    $stats = $stats ?? [];

    $userFlash = $_SESSION['user_flash'] ?? null;
    unset($_SESSION['user_flash']);

    $summaryCards = [
        [
            'title' => 'TOTAL USERS',
            'value' => $stats['total_users'] ?? 0,
            'icon' => 'ri-group-line',
            'color' => 'purple',
            'badge' => 'All',
            'badgeClass' => 'success'
        ],
        [
            'title' => 'ACTIVE',
            'value' => $stats['active_users'] ?? 0,
            'icon' => 'ri-user-follow-line',
            'color' => 'green',
            'badge' => 'Online',
            'badgeClass' => 'success'
        ],
        [
            'title' => 'INACTIVE',
            'value' => $stats['inactive_users'] ?? 0,
            'icon' => 'ri-user-unfollow-line',
            'color' => 'orange',
            'badge' => 'Offline',
            'badgeClass' => 'warning'
        ],
        [
            'title' => 'ADMINS',
            'value' => $stats['admin_users'] ?? 0,
            'icon' => 'ri-shield-user-line',
            'color' => 'red',
            'badge' => 'Role',
            'badgeClass' => 'danger'
        ]
    ];

    $roles = [
        'All',
        'Admin',
        'User'
    ];

    $statuses = [
        'All',
        'Active',
        'Inactive'
    ];

    $columns = [
        'NO',
        'USER',
        'ROLE',
        'STATUS',
        'JOIN DATE',
        'LAST LOGIN',
        'ACTION'
    ];

    if (!function_exists('userInitials')) {
        function userInitials($name)
        {
            $words = preg_split('/\s+/', trim((string)$name));

            $initials = '';

            foreach ($words as $word) {
                if ($word !== '') {
                    $initials .= strtoupper(substr($word, 0, 1));
                }

                if (strlen($initials) >= 2) {
                    break;
                }
            }

            return $initials ?: 'US';
        }
    }
?>

<div class="content">
    <div class="users-container">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Users</h2>
                <p>Manage your organization's members and their access levels</p>
            </div>
            <button
                type="button"
                class="btn-add-user"
                data-bs-toggle="modal"
                data-bs-target="#addUserModal">
                <i class="ri-user-add-line"></i>
                Add User
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <?php foreach ($summaryCards as $card): ?>
                <div class="summary-card">
                    <div class="card-top">
                        <div class="icon <?= $card['color']; ?>">
                            <i class="<?= $card['icon']; ?>"></i>
                        </div>
                        <span class="badge <?= $card['badgeClass']; ?>">
                            <?= $card['badge']; ?>
                        </span>
                    </div>

                    <span class="card-title">
                        <?= $card['title']; ?>
                    </span>

                    <h3>
                        <?= number_format((int)$card['value']); ?>
                    </h3>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <?php if ($userFlash): ?>
                <div class="order-alert order-alert-<?= htmlspecialchars($userFlash['type']); ?>">
                    <span>
                        <?= htmlspecialchars($userFlash['message']); ?>
                    </span>
                    <button
                        type="button"
                        class="order-alert-close"
                        data-order-alert-close>
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            <?php endif; ?>

            <label class="search-order">
                <i class="ri-search-line"></i>
                <input
                    type="text"
                    id="userSearch"
                    placeholder="Search name or email...">
            </label>

            <div class="filter-group">
                <div class="dropdown">
                    <button
                        class="custom-filter dropdown-toggle"
                        id="roleFilter"
                        data-bs-toggle="dropdown">
                        Role: All
                    </button>

                    <ul class="dropdown-menu">
                        <?php foreach ($roles as $role): ?>
                            <li>
                                <a 
                                    class="dropdown-item role-option"
                                    href="#"
                                    data-role="<?= strtolower($role); ?>">
                                    <?= $role; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="dropdown">
                    <button
                        class="custom-filter dropdown-toggle"
                        data-bs-toggle="dropdown">
                        Status: All
                    </button>

                    <ul class="dropdown-menu">
                        <?php foreach ($statuses as $status): ?>
                            <li>
                                <a 
                                    class="dropdown-item status-option"
                                    href="#"
                                    data-status="<?= strtolower($status); ?>">
                                    <?= $status; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="users-table table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th class="<?= $column === 'ACTION' ? 'col-actions' : ''; ?>">
                                <?= $column; ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr
                            class="table-row"
                            data-search="<?= strtolower($user['name'] . ' ' . $user['email']); ?>"
                            data-role="<?= strtolower($user['role']); ?>"
                            data-status="<?= strtolower($user['status']); ?>">


                            <!-- User -->
                             <td class="user-number">
                                <?= $index + 1; ?>
                            </td>
                            <td>
                                <div class="user-cell">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <img
                                            src="assets/uploads/users/<?= htmlspecialchars($user['avatar']); ?>"
                                            class="user-avatar"
                                            alt="<?= htmlspecialchars($user['name']); ?>">
                                    <?php else: ?>
                                        <div class="user-avatar-placeholder">
                                            <?= userInitials($user['name']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="user-info">
                                        <span class="customer-name">
                                            <?= htmlspecialchars($user['name']); ?>
                                        </span>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($user['email']); ?>
                                        </small>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td>
                                <?php
                                $roleClass = strtolower($user['role']) === 'admin'
                                    ? 'badge-admin'
                                    : 'badge-user';
                                ?>
                                <span class="<?= $roleClass; ?>">
                                    <?= htmlspecialchars($user['role']); ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td>
                                <span class="badge-status badge-<?= strtolower($user['status']); ?>">
                                    <?= htmlspecialchars($user['status']); ?>
                                </span>
                            </td>

                            <!-- Join Date -->
                            <td>
                                <?= date('M d, Y', strtotime($user['created_at'])); ?>
                            </td>

                            <!-- Last Login -->
                            <td>
                                <?php if (!empty($user['last_login'])): ?>
                                    <?= date('M d, Y H:i', strtotime($user['last_login'])); ?>
                                <?php else: ?>

                                    <span class="text-muted">
                                        Never Login
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Actions -->
                            <td class="col-actions">
                                <div class="action-buttons action-buttons-right">
                                    <a
                                        href="#"
                                        class="btn-action-icon"
                                        title="View"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailUserModal<?= $user['id']; ?>">
                                        <i class="ri-eye-line"></i>
                                    </a>
                                    <a
                                        href="#"
                                        class="btn-action-icon"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editUserModal<?= $user['id']; ?>">
                                        <i class="ri-pencil-line"></i>

                                    </a>
                                    <a
                                        href="javascript:void(0);"
                                        class="btn-action-icon btn-action-delete"
                                        title="Delete"
                                        data-delete-url="index.php?page=users&action=delete&id=<?= $user['id']; ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteConfirmModal">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="empty-state-cell">
                                <i class="ri-user-line empty-state-icon"></i>
                                No users to display.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-footer">
                <span class="page-info" data-per-page="10">
                    Showing 0 users
                </span>
                <div class="pagination" data-user-pagination></div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>

<!-- Add User Modal -->
<?php include 'views/users/add.php'; ?>


<!-- Detail & Edit Modal -->
<?php foreach ($users as $user): ?>
    <?php include 'views/users/detail.php'; ?>
    <?php include 'views/users/edit.php'; ?>
<?php endforeach; ?>

<!-- Delete Confirmation Modal -->
<?php
    $deleteTitle = "Delete User";
    $deleteMessage = "Are you sure you want to delete this user?";
    include 'views/users/delete.php';
?>

<?php include 'views/layout/footer.php'; ?>