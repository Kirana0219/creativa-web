<?php
$orderStatusOptions = ['pending' => 'Pending', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
$paymentStatusOptions = ['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed'];
$paymentMethodOptions = ['Bank Transfer (BCA)', 'Credit Card', 'Cash on Delivery', 'E-Wallet'];
?>

<div class="modal fade order-modal" id="addOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content order-modal-content">
            <div class="order-modal-header">
                <div>
                    <h3>Add New Order</h3>
                    <p>Create a new order and set its payment information</p>
                </div>
                <button type="button" class="order-modal-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="order-modal-card">
                <form class="order-modal-form" action="index.php?page=orders&action=store" method="POST">
                    <section class="order-modal-section">
                        <h4>Customer Summary</h4>
                        <div class="modal-form-grid">
                            <label class="modal-field">
                                <span>Customer Name</span>
                                <input type="text" name="customer_name" placeholder="Customer name" required>
                            </label>

                            <label class="modal-field">
                                <span>Email</span>
                                <input type="email" name="email" placeholder="customer@email.com" required>
                            </label>
                        </div>

                        <div class="modal-form-grid summary-input-grid">
                            <label class="modal-field">
                                <span>Product</span>
                                <select name="product_id" data-order-product-select required>
                                    <option value="" data-price="0">Select product</option>
                                    <?php foreach ($products as $product): ?>
                                        <option
                                            value="<?= (int) $product['id']; ?>"
                                            data-price="<?= (float) $product['price']; ?>"
                                        >
                                            <?= htmlspecialchars($product['name']); ?> - Rp <?= number_format($product['price'], 0, ',', '.'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="modal-field">
                                <span>Product Price</span>
                                <input type="text" value="Rp 0" data-order-product-price readonly>
                            </label>
                        </div>

                        <div class="modal-form-grid summary-input-grid">
                            <label class="modal-field">
                                <span>Total Items</span>
                                <input type="number" name="total_items" min="1" value="1" data-order-quantity required>
                            </label>

                            <label class="modal-field">
                                <span>Total Amount</span>
                                <input type="number" name="total_amount" min="0" step="1000" value="0" data-order-total-amount readonly required>
                            </label>
                        </div>
                    </section>

                    <section class="order-modal-section">
                        <h4>Order Status & Payment</h4>
                        <div class="modal-form-grid">
                            <label class="modal-field">
                                <span>Order Date</span>
                                <input type="date" name="order_date" value="<?= date('Y-m-d'); ?>" required>
                            </label>

                            <label class="modal-field">
                                <span>Order Status</span>
                                <select name="order_status">
                                    <?php foreach ($orderStatusOptions as $value => $label): ?>
                                        <option value="<?= $value; ?>"><?= $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                            <label class="modal-field">
                                <span>Payment Status</span>
                                <select name="payment_status">
                                    <?php foreach ($paymentStatusOptions as $value => $label): ?>
                                        <option value="<?= $value; ?>"><?= $label; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>

                        </div>
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
