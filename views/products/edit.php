<?php

$product = $product ?? [
    'id' => '',
    'name' => '',
    'category_id' => '',
    'price' => 0,
    'stock' => 0,
    'sku' => '',
    'image' => null,
];
$categories = $categories ?? [];

include 'views/layout/header.php';
include 'views/layout/sidebar.php';
?>

<link rel="stylesheet" href="assets/css/products.css">

<div class="form-wrapper">
    <div class="form-header">
        <h1>Edit Product</h1>
        <p>Modify the details of your existing product listing.</p>
    </div>

    <div class="form-card">
        <form action="index.php?page=products&action=update" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">

            <div class="form-group full-width">
                <label for="name">Product Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input-custom"
                    required
                    value="<?= htmlspecialchars($product['name']) ?>"
                    placeholder="e.g. Premium Arabica Coffee">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category</label>

                    <div class="category-select-group">
                        <select
                            id="category_id"
                            name="category_id"
                            class="form-input-custom">

                            <option value="">Select Category</option>

                            <?php foreach ($categories as $cat): ?>
                                <option
                                    value="<?= $cat['id'] ?>"
                                    data-prefix="<?= htmlspecialchars($cat['sku_prefix'] ?? '') ?>"
                                    <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?><?= !empty($cat['sku_prefix']) ? ' (' . htmlspecialchars($cat['sku_prefix']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>

                        </select>

                        <button
                            type="button"
                            class="btn-quick-add"
                            onclick="openCatModal()"
                            title="Add New Category">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-input-custom"
                        accept="image/*">

                    <?php if (!empty($product['image'])): ?>
                        <div class="image-preview-container">
                            <img
                                src="assets/uploads/<?= htmlspecialchars($product['image']) ?>"
                                class="image-preview"
                                alt="Current Image">

                            <span class="current-image-text">
                                Current: <?= htmlspecialchars($product['image']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group full-width">
                    <label for="sku_preview">SKU</label>

                    <input
                        type="text"
                        id="sku_preview"
                        class="form-input-custom sku-preview-input"
                        readonly
                        value="<?= htmlspecialchars($product['sku'] ?: 'Auto-generated after save') ?>">
                </div>

                <div class="form-group">
                    <label for="price">Price (IDR)</label>

                    <input
                        type="number"
                        id="price"
                        name="price"
                        class="form-input-custom"
                        required
                        min="0"
                        value="<?= (int)$product['price'] ?>"
                        placeholder="e.g. 85000">
                </div>

                <div class="form-group">
                    <label for="stock">Current Stock</label>

                    <input
                        type="number"
                        id="stock"
                        name="stock"
                        class="form-input-custom"
                        required
                        min="0"
                        value="<?= (int)$product['stock'] ?>"
                        placeholder="e.g. 100">
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php?page=products" class="btn-cancel">
                    Cancel
                </a>

                <button type="submit" class="btn-submit">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Category Modal -->
<div class="cat-modal-backdrop" id="catModal">
    <div class="cat-modal">

        <div class="filter-modal-header">
            <h5>New Category</h5>

            <button
                type="button"
                class="filter-modal-close"
                onclick="closeCatModal()">
                &times;
            </button>
        </div>

        <form action="index.php?page=products&action=addCategory" method="POST">

            <div class="filter-modal-body">

                <div class="filter-form-group tight">
                    <label for="cat_name">Category Name</label>

                    <input
                        type="text"
                        id="cat_name"
                        name="name"
                        class="filter-input"
                        placeholder="e.g. Beverages"
                        required>
                </div>

            </div>

            <div class="filter-modal-footer">
                <button
                    type="button"
                    class="btn-filter-reset"
                    onclick="closeCatModal()">
                    Cancel
                </button>

                <button
                    type="submit"
                    class="btn-filter-apply">
                    Create
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function openCatModal() {
    document.getElementById('catModal').classList.add('is-open');
}

function closeCatModal() {
    document.getElementById('catModal').classList.remove('is-open');
}

function updateSkuPreview() {
    const categorySelect = document.getElementById('category_id');
    const skuPreview = document.getElementById('sku_preview');
    const originalCategoryId = <?= json_encode((string) ($product['category_id'] ?? '')) ?>;
    const currentSku = <?= json_encode((string) ($product['sku'] ?? '')) ?>;
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const prefix = selectedOption ? selectedOption.dataset.prefix : '';

    if (categorySelect.value === originalCategoryId && currentSku !== '') {
        skuPreview.value = currentSku;
        return;
    }

    skuPreview.value = prefix ? prefix + '-0001+' : 'PRD-0001+';
}

document.getElementById('category_id').addEventListener('change', updateSkuPreview);
updateSkuPreview();

window.addEventListener('click', function (event) {
    const modal = document.getElementById('catModal');

    if (event.target === modal) {
        closeCatModal();
    }
});
</script>

<?php
include 'views/layout/footer.php';
?>
