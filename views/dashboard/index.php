<?php

$stats = $stats ?? [
    'total_sales' => 0,
    'sales_growth' => 0,
    'total_orders' => 0,
    'orders_growth' => 0,
    'active_products' => 0,
    'low_stock_alerts' => 0,
];
$orders = $orders ?? [];
$page = $page ?? 1;
$limit = $limit ?? 10;
$totalOrdersAll = $totalOrdersAll ?? 0;
$totalPages = $totalPages ?? 1;
$rangeDays = $rangeDays ?? 7;

include 'views/layout/header.php';
include 'views/layout/sidebar.php';

function formatRupiahDash($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

$offset = ($page - 1) * $limit;

$dashboardUrl = function (array $overrides = []) use ($page, $limit, $rangeDays) {
    $params = array_merge([
        'page' => 'dashboard',
        'page_num' => $page,
        'limit' => $limit,
        'range' => $rangeDays,
    ], $overrides);

    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }

    return 'index.php?' . http_build_query($params);
};
?>

<link rel="stylesheet" href="assets/css/dashboard.css">

<div class="catalog-wrapper">

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-rounded" role="alert">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="catalog-header">
        <div class="catalog-title">
            <h1>Good morning, Global Creativa!</h1>
            <p>Here's what's happening with your store today.</p>
        </div>

        <div class="catalog-header-actions">
            <div class="dropdown">
                <button class="btn-action-outline dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="ri-calendar-line"></i>
                    <span><?= $rangeDays == 7 ? 'Last 7 Days' : ($rangeDays == 30 ? 'Last 30 Days' : 'Last ' . $rangeDays . ' Days') ?></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?= htmlspecialchars($dashboardUrl(['range' => 7])) ?>">Last 7 Days</a></li>
                    <li><a class="dropdown-item" href="<?= htmlspecialchars($dashboardUrl(['range' => 30])) ?>">Last 30 Days</a></li>
                    <li><a class="dropdown-item" href="<?= htmlspecialchars($dashboardUrl(['range' => 90])) ?>">Last 90 Days</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
        <!-- Total Sales -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon icon-total">
                    <i class="ri-line-chart-line"></i>
                </div>
                <?php if ($stats['sales_growth'] != 0): ?>
                    <span class="stat-growth <?= $stats['sales_growth'] >= 0 ? 'stat-growth-up' : 'stat-growth-down' ?>">
                        <i class="ri-arrow-<?= $stats['sales_growth'] >= 0 ? 'up' : 'down' ?>-line"></i>
                        <?= $stats['sales_growth'] >= 0 ? '+' : '' ?><?= $stats['sales_growth'] ?>%
                    </span>
                <?php endif; ?>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Sales</span>
                <span class="stat-value"><?= formatRupiahDash($stats['total_sales']) ?></span>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon icon-total">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <?php if ($stats['orders_growth'] != 0): ?>
                    <span class="stat-growth <?= $stats['orders_growth'] >= 0 ? 'stat-growth-up' : 'stat-growth-down' ?>">
                        <i class="ri-arrow-<?= $stats['orders_growth'] >= 0 ? 'up' : 'down' ?>-line"></i>
                        <?= $stats['orders_growth'] >= 0 ? '+' : '' ?><?= $stats['orders_growth'] ?>%
                    </span>
                <?php endif; ?>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Orders</span>
                <span class="stat-value"><?= number_format($stats['total_orders']) ?></span>
            </div>
        </div>

        <!-- Active Products -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon icon-value">
                    <i class="ri-checkbox-circle-line"></i>
                </div>
                <span class="badge badge-success">Active</span>
            </div>
            <div class="stat-details">
                <span class="stat-label">Active Products</span>
                <span class="stat-value"><?= number_format($stats['active_products']) ?></span>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon icon-low">
                    <i class="ri-error-warning-line"></i>
                </div>
                <span class="badge badge-danger">Urgent</span>
            </div>
            <div class="stat-details">
                <span class="stat-label">Low Stock Alerts</span>
                <span class="stat-value stat-value-danger"><?= number_format($stats['low_stock_alerts']) ?></span>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="table-container">
        <table class="product-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <!-- <th class="col-actions">Action</th> -->
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="8" class="empty-state-cell">
                            <i class="ri-inbox-line empty-state-icon"></i>
                            Belum ada order.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td class="order-id">
                                <a href="index.php?page=orders&action=view&id=<?= $order['id'] ?>">
                                    #<?= htmlspecialchars($order['order_code']) ?>
                                </a>
                            </td>
                            <td>
                                <div class="customer-cell">
                                    <span class="customer-name"><?= htmlspecialchars($order['customer_name']) ?></span>
                                </div>
                            </td>
                            <td><?= date('M d, Y', strtotime($order['order_date'])) ?></td>
                            <td>
                                <div class="product-info-cell">
                                    <?php if (!empty($order['product_image'])): ?>
                                        <img src="assets/uploads/<?= htmlspecialchars($order['product_image']) ?>" class="product-thumbnail" alt="">
                                    <?php else: ?>
                                        <div class="product-thumbnail product-thumbnail-placeholder">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($order['product'] ?? 'No product') ?></span>
                                </div>
                            </td>
                            <td class="order-amount"><?= formatRupiahDash($order['total_amount']) ?></td>
                            <td>
                                <span class="badge-paid"><?= htmlspecialchars(ucfirst($order['payment_status'])) ?></span>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= htmlspecialchars($order['order_status']) ?>">
                                    <?= htmlspecialchars(ucfirst($order['order_status'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Footer -->
        <div class="pagination-footer">
            <span class="page-info">
                <?php if ($totalOrdersAll > 0): ?>
                    Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $totalOrdersAll) ?> of <?= number_format($totalOrdersAll) ?> orders
                <?php else: ?>
                    No orders found
                <?php endif; ?>
            </span>

            <div class="pagination-controls">
                <a href="<?= htmlspecialchars($dashboardUrl(['page_num' => max(1, $page - 1)])) ?>" class="btn-page-nav <?= $page == 1 ? 'disabled' : '' ?>">Previous</a>

                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                if ($startPage > 1) {
                    echo '<a href="' . htmlspecialchars($dashboardUrl(['page_num' => 1])) . '" class="btn-page-nav">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-dots">...</span>';
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo '<a href="' . htmlspecialchars($dashboardUrl(['page_num' => $i])) . '" class="btn-page-nav ' . $activeClass . '">' . $i . '</a>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="page-dots">...</span>';
                    }
                    echo '<a href="' . htmlspecialchars($dashboardUrl(['page_num' => $totalPages])) . '" class="btn-page-nav">' . $totalPages . '</a>';
                }
                ?>

                <a href="<?= htmlspecialchars($dashboardUrl(['page_num' => min($totalPages, $page + 1)])) ?>" class="btn-page-nav <?= $page == $totalPages ? 'disabled' : '' ?>">Next</a>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>