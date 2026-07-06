<?php
include 'views/layout/header.php';
include 'views/layout/sidebar.php';

$orders = $orders ?? [];
$stats = $stats ?? [];
$summaryCards = [
    ['title' => 'TOTAL ORDERS', 'value' => $stats['total_orders'] ?? 0, 'icon' => 'ri-file-list-3-line', 'color' => 'purple', 'badge' => 'All', 'badgeClass' => 'success'],
    ['title' => 'PENDING', 'value' => $stats['pending_orders'] ?? 0, 'icon' => 'ri-time-line', 'color' => 'orange', 'badge' => 'Active', 'badgeClass' => 'warning'],
    ['title' => 'SHIPPED', 'value' => $stats['shipped_orders'] ?? 0, 'icon' => 'ri-truck-line', 'color' => 'green', 'badge' => 'Sent', 'badgeClass' => 'success'],
    ['title' => 'CANCELLED', 'value' => $stats['cancelled_orders'] ?? 0, 'icon' => 'ri-close-circle-line', 'color' => 'red', 'badge' => 'Void', 'badgeClass' => 'danger'],
];

$statuses = ['All', 'Pending', 'Shipped', 'Delivered', 'Cancelled'];
$columns = ['ORDER ID', 'CUSTOMER', 'DATE', 'PRODUCT', 'AMOUNT', 'PAYMENT', 'STATUS', 'ACTION'];
$ordersPerPage = 10;
?>

<div class="content">
    <div class="orders-container">
        <div class="page-header">
            <div>
                <h2>Orders</h2>
                <p>Manage and track your business transactions</p>
            </div>

            <button class="btn-export">
                <i class="ri-download-line"></i>
                Export Report
            </button>
        </div>

        <div class="summary-cards">
            <?php foreach ($summaryCards as $card): ?>
                <div class="summary-card">
                    <div class="card-top">
                        <div class="icon <?= $card['color']; ?>">
                            <i class="<?= $card['icon']; ?>"></i>
                        </div>
                        <span class="badge <?= $card['badgeClass']; ?>"><?= $card['badge']; ?></span>
                    </div>

                    <span class="card-title"><?= $card['title']; ?></span>
                    <h3><?= number_format((int) $card['value']); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="filter-section">
            <label class="search-order">
                <i class="ri-search-line"></i>
                <input type="text" placeholder="Search order ID, customer, or product...">
            </label>

            <div class="filter-group">
                <div class="dropdown">
                    <button class="custom-filter dropdown-toggle" data-bs-toggle="dropdown">
                        Status: All
                    </button>

                    <ul class="dropdown-menu">
                        <?php foreach ($statuses as $status): ?>
                            <li><a class="dropdown-item" href="#"><?= $status; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <label class="date-filter">
                    <i class="ri-calendar-line"></i>
                    <input id="dateRange" type="text" placeholder="Select Date" readonly>
                </label>
            </div>
        </div>

        <div class="orders-table">
            <div class="table-header">
                <?php foreach ($columns as $column): ?>
                    <div><?= $column; ?></div>
                <?php endforeach; ?>
            </div>

            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="table-row" data-status="<?= strtolower($order['order_status']); ?>" data-date="<?= $order['order_date']; ?>">
                        <div class="order-id">
                            <a href="#"><?= htmlspecialchars($order['order_code']); ?></a>
                        </div>

                        <div class="customer">
                            <strong><?= htmlspecialchars($order['customer_name']); ?></strong>
                            <span><?= htmlspecialchars($order['email']); ?></span>
                        </div>

                        <div><?= date('M d, Y', strtotime($order['order_date'])); ?></div>

                        <div class="product">
                            <?php if (!empty($order['product_image'])): ?>
                                <img src="assets/uploads/<?= htmlspecialchars($order['product_image']); ?>" alt="">
                            <?php else: ?>
                                <span class="product-placeholder"><i class="ri-image-line"></i></span>
                            <?php endif; ?>
                            <span><?= htmlspecialchars($order['product'] ?? 'No product'); ?></span>
                        </div>

                        <div>Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></div>
                        <div><span class="badge-paid"><?= htmlspecialchars(ucfirst($order['payment_status'])); ?></span></div>
                        <div><span class="badge-status"><?= htmlspecialchars(ucfirst($order['order_status'])); ?></span></div>

                        <div class="actions">
                            <i class="ri-eye-line"></i>
                            <i class="ri-more-2-fill"></i>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-orders">No orders to display.</div>
            <?php endif; ?>

            <div class="table-footer">
                <span class="page-info" data-per-page="5">Showing 0 orders</span>
                <div class="pagination" data-pagination></div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layout/footer.php'; ?>
