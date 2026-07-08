<?php
$orderProducts = array_filter(array_map('trim', explode(',', $order['product'] ?? '')));
$totalItems = (int) ($order['total_items'] ?? count($orderProducts));
$customerInitials = orderInitials($order['customer_name'] ?? '');
$internalNotes = trim((string) ($order['internal_notes'] ?? ''));
?>

<div class="modal fade order-modal" id="detailOrderModal<?= $order['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content order-modal-content">
            <div class="order-modal-header">
                <div>
                    <h3>Order Detail #<?= htmlspecialchars($order['order_code']); ?></h3>
                    <p>Review order information for <?= htmlspecialchars($order['customer_name']); ?></p>
                </div>
                <button type="button" class="order-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="order-modal-card">
                <section class="order-modal-section">
                    <h4>Customer Summary</h4>
                    <div class="customer-summary customer-summary-detail">
                        <span class="customer-avatar"><?= htmlspecialchars($customerInitials); ?></span>
                        <div class="customer-summary-text">
                            <strong><?= htmlspecialchars($order['customer_name']); ?></strong>
                            <span><?= htmlspecialchars($order['email']); ?></span>
                        </div>
                    </div>

                    <div class="summary-lines">
                        <div>
                            <span>Total Items</span>
                            <strong><?= number_format($totalItems); ?> Item<?= $totalItems === 1 ? '' : 's'; ?></strong>
                        </div>
                        <div>
                            <span>Total Amount</span>
                            <strong class="summary-amount">Rp <?= number_format($order['total_amount'], 0, ',', '.'); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="order-modal-section">
                    <h4>Order Information</h4>
                    <div class="detail-grid">
                        <div class="detail-field">
                            <span>Order Status</span>
                            <strong><?= htmlspecialchars(ucfirst($order['order_status'])); ?></strong>
                        </div>
                        <div class="detail-field">
                            <span>Payment Status</span>
                            <strong><?= htmlspecialchars(ucfirst($order['payment_status'])); ?></strong>
                        </div>
                        <div class="detail-field">
                            <span>Order Date</span>
                            <strong><?= date('M d, Y', strtotime($order['order_date'])); ?></strong>
                        </div>
                        <div class="detail-field">
                            <span>Products</span>
                            <strong><?= htmlspecialchars($order['product'] ?? 'No product'); ?></strong>
                        </div>
                    </div>
                </section>

                <section class="order-modal-section">
                    <h4>Internal Notes</h4>

                    <div class="detail-notes">
                        <?= !empty($order['internal_notes'])
                            ? nl2br(htmlspecialchars($order['internal_notes']))
                            : '<span class="text-muted">No internal notes available.</span>'; ?>
                    </div>
                </section>

                <div class="modal-actions">
                    <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
