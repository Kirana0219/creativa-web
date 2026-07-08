<?php
$orderProducts = array_filter(array_map('trim', explode(',', $order['product'] ?? '')));
$totalItems = (int) ($order['total_items'] ?? count($orderProducts));
$orderStatusOptions = ['pending' => 'Pending', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
$paymentStatusOptions = ['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'];
$paymentMethodOptions = ['Bank Transfer (BCA)', 'Credit Card', 'Cash on Delivery', 'E-Wallet'];
?>

<div class="modal fade order-modal" id="editOrderModal<?= $order['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content order-modal-content">
            <div class="order-modal-header">
                <div>
                    <h3>Manage Order #<?= htmlspecialchars($order['order_code']); ?></h3>
                    <p>Update order status and payment information for <?= htmlspecialchars($order['customer_name']); ?></p>
                </div>
                <button type="button" class="order-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="order-modal-card">
                <form class="order-modal-form" action="index.php?page=orders&action=update" method="POST">
                    <input type="hidden" name="order_id" value="<?= $order['id']; ?>">

                    <section class="order-modal-section">
                        <h4>Order Summary</h4>
                        <div class="modal-form-grid">
                            <label class="modal-field">
                                <span>Total Items</span>
                                <input type="number" name="total_items" min="0" value="<?= (int) ($order['total_items'] ?? $totalItems); ?>" required>
                            </label>

                            <label class="modal-field">
                                <span>Total Amount</span>
                                <input type="number" name="total_amount" min="0" step="1000" value="<?= (float) $order['total_amount']; ?>" required>
                            </label>
                        </div>
                    </section>

                    <section class="order-modal-section">
                        <h4>Order Status & Payment</h4>
                        <div class="modal-form-grid">
                            <label class="modal-field">
                                <span>Order Date</span>
                                <input type="date" name="order_date" value="<?= htmlspecialchars($order['order_date']); ?>" required>
                            </label>

                            <label class="modal-field">
                                <span>Order Status</span>
                                <select name="order_status">
                                    <?php foreach ($orderStatusOptions as $value => $label): ?>
                                        <option value="<?= $value; ?>" <?= $order['order_status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="modal-field">
                                <span>Payment Status</span>
                                <select name="payment_status">
                                    <?php foreach ($paymentStatusOptions as $value => $label): ?>
                                        <option value="<?= $value; ?>" <?= $order['payment_status'] === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                    </section> 

                    <section class="order-modal-section">
                        <h4>Internal Notes</h4>

                        <textarea
                            name="internal_notes"
                            placeholder="Add a note for internal tracking..."
                    ><?= htmlspecialchars($order['internal_notes'] ?? ''); ?></textarea>
                    </section>

                    <div class="modal-actions">
                        <button type="button" class="btn-modal-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-modal-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
