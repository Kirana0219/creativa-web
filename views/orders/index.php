<?php
include 'views/layout/header.php';
include 'views/layout/sidebar.php';

$orders = $orders ?? [];
$products = $products ?? [];
$stats = $stats ?? [];
$orderFlash = $_SESSION['order_flash'] ?? null;
unset($_SESSION['order_flash']);

$summaryCards = [
    ['title' => 'TOTAL ORDERS', 'value' => $stats['total_orders'] ?? 0, 'icon' => 'ri-file-list-3-line', 'color' => 'purple', 'badge' => 'All', 'badgeClass' => 'success'],
    ['title' => 'PENDING', 'value' => $stats['pending_orders'] ?? 0, 'icon' => 'ri-time-line', 'color' => 'orange', 'badge' => 'Active', 'badgeClass' => 'warning'],
    ['title' => 'SHIPPED', 'value' => $stats['shipped_orders'] ?? 0, 'icon' => 'ri-truck-line', 'color' => 'green', 'badge' => 'Sent', 'badgeClass' => 'success'],
    ['title' => 'CANCELLED', 'value' => $stats['cancelled_orders'] ?? 0, 'icon' => 'ri-close-circle-line', 'color' => 'red', 'badge' => 'Void', 'badgeClass' => 'danger'],
];

$statuses = ['All', 'Pending', 'Shipped', 'Delivered', 'Cancelled'];
$columns = ['ORDER ID', 'CUSTOMER', 'DATE', 'PRODUCT', 'AMOUNT', 'PAYMENT', 'STATUS', 'ACTION'];
$ordersPerPage = 10;

if (!function_exists('orderInitials')) {
    function orderInitials($name)
    {
        $words = preg_split('/\s+/', trim((string) $name));
        $initials = '';

        foreach ($words as $word) {
            if ($word !== '') {
                $initials .= strtoupper(substr($word, 0, 1));
            }

            if (strlen($initials) >= 2) {
                break;
            }
        }

        return $initials ?: 'CO';
    }
}
?>

<div class="content">
    <div class="orders-container">
        <div class="page-header">
            <div>
                <h2>Orders</h2>
                <p>Manage and track your business transactions</p>
            </div>

            <button type="button" class="btn-add-order" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="ri-add-line"></i>
                Add Order
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
            <?php if ($orderFlash): ?>
                <div class="order-alert order-alert-<?= htmlspecialchars($orderFlash['type']); ?>">
                    <span><?= htmlspecialchars($orderFlash['message']); ?></span>
                    <button type="button" class="order-alert-close" aria-label="Close notification" data-order-alert-close>
                        <i class="ri-close-line"></i>
                    </button>
                </div>
            <?php endif; ?>

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

        <div class="orders-table table-container">
            <table class="product-table">
                <thead>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <th class="<?= $column === 'ACTION' ? 'col-actions' : ''; ?>"><?= $column; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr class="table-row" data-status="<?= strtolower($order['order_status']); ?>" data-date="<?= $order['order_date']; ?>">
                                <td class="order-id">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#detailOrderModal<?= $order['id']; ?>">
                                        #<?= htmlspecialchars($order['order_code']); ?>
                                    </a>
                                </td>

                                <td>
                                    <div class="customer-cell">
                                        <span class="customer-name"><?= htmlspecialchars($order['customer_name']); ?></span>
                                    </div>
                                </td>

                                <td><?= date('M d, Y', strtotime($order['order_date'])); ?></td>

                                <td>
                                    <div class="product-info-cell">
                                        <?php if (!empty($order['product_image'])): ?>
                                            <img src="assets/uploads/<?= htmlspecialchars($order['product_image']); ?>" class="product-thumbnail" alt="">
                                        <?php else: ?>
                                            <span class="product-thumbnail product-thumbnail-placeholder"><i class="ri-image-line"></i></span>
                                        <?php endif; ?>
                                        <span><?= htmlspecialchars($order['product'] ?? 'No product'); ?></span>
                                    </div>
                                </td>

                                <td class="order-amount">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                <td><span class="badge-paid"><?= htmlspecialchars(ucfirst($order['payment_status'])); ?></span></td>
                                <td>
                                    <span class="badge-status badge-<?= htmlspecialchars($order['order_status']); ?>">
                                        <?= htmlspecialchars(ucfirst($order['order_status'])); ?>
                                    </span>
                                </td>

                                <td class="col-actions">
                                    <div class="action-buttons action-buttons-right">
                                        <a href="#" class="btn-action-icon" title="Lihat" data-bs-toggle="modal" data-bs-target="#detailOrderModal<?= $order['id']; ?>">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        <a href="#" class="btn-action-icon" title="Edit" data-bs-toggle="modal" data-bs-target="#editOrderModal<?= $order['id']; ?>">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                        <a href="javascript:void(0);" class="btn-action-icon btn-action-delete" title="Delete" data-delete-url="index.php?page=orders&action=delete&id=<?= $order['id']; ?>" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">
                                            <i class="ri-delete-bin-line"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="empty-state-cell">
                                <i class="ri-inbox-line empty-state-icon"></i>
                                No orders to display.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="pagination-footer">
                <span class="page-info" data-per-page="5">Showing 0 orders</span>
                <div class="pagination" data-pagination></div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/script.js"></script>

<?php include 'views/orders/add.php'; ?>

<?php foreach ($orders as $order): ?>
    <?php include 'views/orders/detail.php'; ?>
    <?php include 'views/orders/edit.php'; ?>
<?php endforeach; ?>

<?php
$deleteTitle = "Delete Order";
$deleteMessage = "Are you sure you want to delete this order?";
include 'views/orders/delete.php';
?>

<?php include 'views/layout/footer.php'; ?>