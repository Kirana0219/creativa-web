<?php

$search = $search ?? '';
$categoryId = $categoryId ?? 0;
$status = $status ?? '';
$sort = $sort ?? 'newest';
$limit = $limit ?? 10;
$page = $page ?? 1;
$totalPages = $totalPages ?? 1;
$totalProductsFiltered = $totalProductsFiltered ?? 0;
$offset = $offset ?? 0;
$products = $products ?? [];
$categories = $categories ?? [];
$stats = $stats ?? [
    'total_products' => 0,
    'low_stock_items' => 0,
    'total_inventory_value' => 0,
    'total_categories' => 0
];

include 'views/layout/header.php';
include 'views/layout/sidebar.php';

// Helper to format currency
function formatRupiah($number) {
    return 'Rp ' . number_format($number, 0, ',', '.');
}

// Helper to format large numbers (e.g. 45.2M)
function formatShortNumber($number) {
    if ($number >= 1000000000) {
        return 'Rp ' . round($number / 1000000000, 1) . 'B';
    } elseif ($number >= 1000000) {
        return 'Rp ' . round($number / 1000000, 1) . 'M';
    }
    return formatRupiah($number);
}

$productUrl = function(array $overrides = []) use ($search, $categoryId, $status, $sort, $limit) {
    $params = array_merge([
        'page' => 'products',
        'search' => $search,
        'category_id' => $categoryId,
        'status' => $status,
        'sort' => $sort,
        'limit' => $limit,
    ], $overrides);

    foreach ($params as $key => $value) {
        if ($value === '' || $value === null) {
            unset($params[$key]);
        }
    }

    return 'index.php?' . http_build_query($params);
};
?>

<link rel="stylesheet" href="assets/css/products.css">

<div class="catalog-wrapper">

    <!-- Alert Success/Error -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-rounded" role="alert">
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show alert-rounded" role="alert">
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Header Section -->
    <div class="catalog-header">
        <div class="catalog-title">
            <h1>Product Catalog</h1>
            <p>Manage your stock, prices, and product visibility from one place.</p>
        </div>
        <button type="button" class="btn-add-product" onclick="openAddProductModal()">
            <i class="ri-add-line icon-lg"></i>
            <span>Add Product</span>
        </button>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
        <!-- Stat Card 1 -->
        <div class="stat-card">
            <div class="stat-icon icon-total">
                <i class="ri-shopping-bag-3-line"></i>
            </div>
            <div class="stat-details">
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?= number_format($stats['total_products'] ?? 0) ?></div>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div class="stat-card">
            <div class="stat-icon icon-low">
                <i class="ri-error-warning-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Low Stock Items</span>
                <span class="stat-value"><?= number_format($stats['low_stock_items'] ?? 0) ?></span>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div class="stat-card">
            <div class="stat-icon icon-value">
                <i class="ri-wallet-3-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Inventory Value</span>
                <span class="stat-value"><?= formatShortNumber($stats['total_inventory_value'] ?? 0) ?></span>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div class="stat-card">
            <div class="stat-icon icon-categories">
                <i class="ri-shapes-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Categories</span>
                <span class="stat-value"><?= number_format($stats['total_categories'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <!-- Table Container Card -->
    <div class="table-container">
        <!-- Toolbar -->
        <div class="table-toolbar">
            <div class="toolbar-left">
                <!-- Filters Button -->
                <button type="button" class="btn-action-outline" onclick="openFilterModal()">
                    <i class="ri-filter-3-line"></i>
                    <span>Filters</span>
                </button>

                <!-- Sort Button Dropdown style (trigger reload on change) -->
                <select class="select-custom" onchange="applySorting(this.value)">
                    <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
                    <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Sort: Oldest</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>

                <!-- Status Tabs -->
                <div class="tab-pills">
                    <a href="<?= htmlspecialchars($productUrl(['status' => '', 'page_num' => 1])) ?>" class="tab-pill-link <?= empty($status) ? 'active' : '' ?>">All Items</a>
                    <a href="<?= htmlspecialchars($productUrl(['status' => 'in_stock', 'page_num' => 1])) ?>" class="tab-pill-link <?= $status == 'in_stock' ? 'active' : '' ?>">In Stock</a>
                    <a href="<?= htmlspecialchars($productUrl(['status' => 'low_stock', 'page_num' => 1])) ?>" class="tab-pill-link <?= $status == 'low_stock' ? 'active' : '' ?>">Low Stock</a>
                </div>
            </div>

            <div class="toolbar-right">
                <?php if ($totalProductsFiltered > 0): ?>
                    Showing <?= $offset + 1 ?> - <?= min($offset + $limit, $totalProductsFiltered) ?> of <?= number_format($totalProductsFiltered) ?> products
                <?php else: ?>
                    No products found
                <?php endif; ?>
            </div>
        </div>

        <!-- Table -->
        <table class="product-table">
            <thead>
                <tr>
                    <th class="number-col">No</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" class="empty-state-cell">
                            <i class="ri-inbox-line empty-state-icon"></i>
                            Belum ada produk. Silakan tambahkan produk baru!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $index => $p): ?>
                        <?php
                            $summaryData = [
                                'name' => $p['name'] ?? '-',
                                'sku' => $p['sku'] ?? '-',
                                'category' => $p['category_name'] ?? 'Uncategorized',
                                'price' => formatRupiah($p['price'] ?? 0),
                                'stock' => number_format($p['stock'] ?? 0) . ' units',
                                'status' => ($p['status'] ?? '') == 'in_stock' ? 'In Stock' : (($p['status'] ?? '') == 'low_stock' ? 'Low Stock' : 'Out of Stock'),
                                'image' => !empty($p['image']) ? 'assets/uploads/' . $p['image'] : '',
                                'created_at' => !empty($p['created_at']) ? date('M d, Y', strtotime($p['created_at'])) : '-',
                                'updated_at' => !empty($p['updated_at']) ? date('M d, Y', strtotime($p['updated_at'])) : '-',
                            ];
                            $editData = [
                                'id' => $p['id'] ?? '',
                                'name' => $p['name'] ?? '',
                                'sku' => $p['sku'] ?? '',
                                'category_id' => (string) ($p['category_id'] ?? ''),
                                'price' => $p['price'] ?? 0,
                                'stock' => $p['stock'] ?? 0,
                                'image' => !empty($p['image']) ? 'assets/uploads/' . $p['image'] : '',
                            ];
                        ?>
                        <tr>
                            <td class="number-col">
                                <?= $offset + $index + 1 ?>
                            </td>
                            <td>
                                <div class="product-info-cell">
                                    <?php if ($p['image']): ?>
                                        <img src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" class="product-thumbnail" alt="<?= htmlspecialchars($p['name']) ?>">
                                    <?php else: ?>
                                        <div class="product-thumbnail product-thumbnail-placeholder">
                                            <i class="ri-image-line"></i>
                                        </div>
                                    <?php endif; ?>
                                    <button
                                        type="button"
                                        class="product-name-text product-name-button"
                                        data-product='<?= htmlspecialchars(json_encode($editData), ENT_QUOTES, 'UTF-8') ?>'
                                        onclick="openEditProductModal(this)">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <span class="sku-text"><?= htmlspecialchars($p['sku'] ?? '-') ?></span>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?>
                            </td>
                            <td>
                                <span class="price-text"><?= formatRupiah($p['price']) ?></span>
                            </td>
                            <td>
                                <span class="stock-text"><?= number_format($p['stock']) ?> units</span>
                            </td>
                            <td>
                                <span class="badge-status badge-<?= htmlspecialchars($p['status']) ?>">
                                    <?= $p['status'] == 'in_stock' ? 'In Stock' : ($p['status'] == 'low_stock' ? 'Low Stock' : 'Out of Stock') ?>
                                </span>
                            </td>
                            <td class="col-actions">
                                <div class="action-buttons action-buttons-right">
                                    <button
                                        type="button"
                                        class="btn-action-icon"
                                        title="View Summary"
                                        data-product='<?= htmlspecialchars(json_encode($summaryData), ENT_QUOTES, 'UTF-8') ?>'
                                        onclick="openProductSummary(this)">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-action-icon"
                                        title="Edit"
                                        data-product='<?= htmlspecialchars(json_encode($editData), ENT_QUOTES, 'UTF-8') ?>'
                                        onclick="openEditProductModal(this)">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-action-icon btn-action-delete"
                                        onclick="confirmDelete(<?= $p['id'] ?>)">
                                        <i class="ri-delete-bin-line"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination Footer -->
        <div class="pagination-footer">
            <div class="rows-per-page">
                <span>Rows per page:</span>
                <select class="select-custom" onchange="changeLimit(this.value)">
                    <option value="5" <?= $limit == 5 ? 'selected' : '' ?>>5</option>
                    <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
                    <option value="20" <?= $limit == 20 ? 'selected' : '' ?>>20</option>
                    <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
                </select>
            </div>

            <div class="pagination-controls">
                <!-- First Page & Prev -->
                <a href="<?= htmlspecialchars($productUrl(['page_num' => 1])) ?>" class="btn-page-nav <?= $page == 1 ? 'disabled' : '' ?>">
                    <i class="ri-skip-back-line"></i>
                </a>
                <a href="<?= htmlspecialchars($productUrl(['page_num' => max(1, $page - 1)])) ?>" class="btn-page-nav <?= $page == 1 ? 'disabled' : '' ?>">
                    <i class="ri-arrow-left-s-line"></i>
                </a>

                <!-- Pages numbers -->
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);

                if ($startPage > 1) {
                    echo '<a href="'.htmlspecialchars($productUrl(['page_num' => 1])).'" class="btn-page-nav">1</a>';
                    if ($startPage > 2) {
                        echo '<span class="page-dots">...</span>';
                    }
                }

                for ($i = $startPage; $i <= $endPage; $i++) {
                    $activeClass = ($i == $page) ? 'active' : '';
                    echo '<a href="'.htmlspecialchars($productUrl(['page_num' => $i])).'" class="btn-page-nav '.$activeClass.'">'.$i.'</a>';
                }

                if ($endPage < $totalPages) {
                    if ($endPage < $totalPages - 1) {
                        echo '<span class="page-dots">...</span>';
                    }
                    echo '<a href="'.htmlspecialchars($productUrl(['page_num' => $totalPages])).'" class="btn-page-nav">'.$totalPages.'</a>';
                }
                ?>

                <!-- Next & Last Page -->
                <a href="<?= htmlspecialchars($productUrl(['page_num' => min($totalPages, $page + 1)])) ?>" class="btn-page-nav <?= $page == $totalPages ? 'disabled' : '' ?>">
                    <i class="ri-arrow-right-s-line"></i>
                </a>
                <a href="<?= htmlspecialchars($productUrl(['page_num' => $totalPages])) ?>" class="btn-page-nav <?= $page == $totalPages ? 'disabled' : '' ?>">
                    <i class="ri-skip-forward-line"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="filter-modal-backdrop" id="editProductModal">
    <div class="filter-modal product-form-modal">
        <div class="filter-modal-header">
            <h5>Edit Product</h5>
            <button type="button" class="filter-modal-close" onclick="closeEditProductModal()">&times;</button>
        </div>

        <form action="index.php?page=products&action=update" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit_id" name="id">

            <div class="filter-modal-body">
                <div class="form-group full-width">
                    <label for="edit_name">Product Name</label>
                    <input type="text" id="edit_name" name="name" class="form-input-custom" required>
                </div>

                <div class="form-row modal-form-row">
                    <div class="form-group">
                        <label for="edit_category_id">Category</label>
                        <select id="edit_category_id" name="category_id" class="form-input-custom">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" data-prefix="<?= htmlspecialchars($cat['sku_prefix'] ?? '') ?>">
                                    <?= htmlspecialchars($cat['name']) ?><?= !empty($cat['sku_prefix']) ? ' (' . htmlspecialchars($cat['sku_prefix']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit_sku_preview">SKU yang diedit</label>
                        <input type="text" id="edit_sku_preview" class="form-input-custom sku-preview-input" readonly>
                        <span class="sku-edit-note" id="edit_sku_note"></span>
                    </div>
                </div>

                <div class="form-row modal-form-row">
                    <div class="form-group">
                        <label for="edit_price">Price (IDR)</label>
                        <input type="number" id="edit_price" name="price" class="form-input-custom" required min="0">
                    </div>

                    <div class="form-group">
                        <label for="edit_stock">Current Stock</label>
                        <input type="number" id="edit_stock" name="stock" class="form-input-custom" required min="0">
                    </div>
                </div>

                <div class="form-group tight">
                    <label for="edit_image">Product Image</label>
                    <input type="file" id="edit_image" name="image" class="form-input-custom" accept="image/*">
                    <div class="image-preview-container" id="edit_image_preview_wrap">
                        <img src="" class="image-preview" id="edit_image_preview" alt="Current Image">
                        <span class="current-image-text">Current image</span>
                    </div>
                </div>
            </div>

            <div class="filter-modal-footer">
                <button type="button" class="btn-filter-reset" onclick="closeEditProductModal()">Cancel</button>
                <button type="submit" class="btn-filter-apply">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Product Modal -->
<div class="filter-modal-backdrop" id="addProductModal">
    <div class="filter-modal product-form-modal">
        <div class="filter-modal-header">
            <h5>Add Product</h5>
            <button type="button" class="filter-modal-close" onclick="closeAddProductModal()">&times;</button>
        </div>

        <form action="index.php?page=products&action=store" method="POST" enctype="multipart/form-data">
            <div class="filter-modal-body">
                <div class="form-group full-width">
                    <label for="modal_name">Product Name</label>
                    <input type="text" id="modal_name" name="name" class="form-input-custom" required placeholder="e.g. Premium Arabica Coffee">
                </div>

                <div class="form-row modal-form-row">
                    <div class="form-group">
                        <label for="modal_category_id">Category</label>
                        <select id="modal_category_id" name="category_id" class="form-input-custom">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" data-prefix="<?= htmlspecialchars($cat['sku_prefix'] ?? '') ?>">
                                    <?= htmlspecialchars($cat['name']) ?><?= !empty($cat['sku_prefix']) ? ' (' . htmlspecialchars($cat['sku_prefix']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_sku_preview">SKU</label>
                        <input type="text" id="modal_sku_preview" class="form-input-custom sku-preview-input" value="PRD-0001+" readonly>
                    </div>
                </div>

                <div class="form-row modal-form-row">
                    <div class="form-group">
                        <label for="modal_price">Price (IDR)</label>
                        <input type="number" id="modal_price" name="price" class="form-input-custom" required min="0" placeholder="e.g. 85000">
                    </div>

                    <div class="form-group">
                        <label for="modal_stock">Initial Stock</label>
                        <input type="number" id="modal_stock" name="stock" class="form-input-custom" required min="0" placeholder="e.g. 100">
                    </div>
                </div>

                <div class="form-group tight">
                    <label for="modal_image">Product Image</label>
                    <input type="file" id="modal_image" name="image" class="form-input-custom" accept="image/*">
                </div>
            </div>

            <div class="filter-modal-footer">
                <button type="button" class="btn-filter-reset" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn-filter-apply">Save Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Product Summary Modal -->
<div class="filter-modal-backdrop" id="productSummaryModal">
    <div class="filter-modal product-summary-modal">
        <div class="filter-modal-header">
            <h5>Product Summary</h5>
            <button type="button" class="filter-modal-close" onclick="closeProductSummary()">&times;</button>
        </div>

        <div class="filter-modal-body">
            <div class="summary-product-head">
                <div class="summary-image" id="summaryImage">
                    <i class="ri-image-line"></i>
                </div>
                <div>
                    <h3 id="summaryName">-</h3>
                    <span class="sku-text" id="summarySku">-</span>
                </div>
            </div>

            <div class="summary-grid">
                <div class="summary-item">
                    <span>Category</span>
                    <strong id="summaryCategory">-</strong>
                </div>
                <div class="summary-item">
                    <span>Price</span>
                    <strong id="summaryPrice">-</strong>
                </div>
                <div class="summary-item">
                    <span>Stock</span>
                    <strong id="summaryStock">-</strong>
                </div>
                <div class="summary-item">
                    <span>Status</span>
                    <strong id="summaryStatus">-</strong>
                </div>
                <div class="summary-item">
                    <span>Created</span>
                    <strong id="summaryCreated">-</strong>
                </div>
                <div class="summary-item">
                    <span>Updated</span>
                    <strong id="summaryUpdated">-</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal Backdrop -->
<div class="filter-modal-backdrop" id="filterModal">
    <div class="filter-modal">
        <div class="filter-modal-header">
            <h5>Filters</h5>
            <button type="button" class="filter-modal-close" onclick="closeFilterModal()">&times;</button>
        </div>
        <form action="index.php" method="GET">
            <input type="hidden" name="page" value="products">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
            <input type="hidden" name="limit" value="<?= htmlspecialchars($limit) ?>">

            <div class="filter-modal-body">
                <div class="filter-form-group">
                    <label>Search Product</label>
                    <input type="text" name="search" class="filter-input" placeholder="Search name, SKU, or category..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-form-group">
                    <label>Category</label>
                    <select name="category_id" class="filter-input">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?><?= !empty($cat['sku_prefix']) ? ' (' . htmlspecialchars($cat['sku_prefix']) . ')' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="filter-modal-footer">
                <button type="button" class="btn-filter-reset" onclick="resetFilters()">Reset Filters</button>
                <button type="submit" class="btn-filter-apply">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<script>
function confirmDelete(id) {

    Swal.fire({
        title: 'Delete Product?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {

        if (result.isConfirmed) {

            window.location.href =
                'index.php?page=products&action=delete&id=' + id;

        }

    });

}
    // Modal Control
    function openFilterModal() {
        document.getElementById('filterModal').classList.add('is-open');
    }

    function closeFilterModal() {
        document.getElementById('filterModal').classList.remove('is-open');
    }

    // Modal click-outside close
    window.onclick = function(event) {
        const modals = ['filterModal', 'addProductModal', 'editProductModal', 'productSummaryModal'];
        modals.forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (event.target === modal) {
                modal.classList.remove('is-open');
            }
        });
    }

    function openAddProductModal() {
        document.getElementById('addProductModal').classList.add('is-open');
    }

    function closeAddProductModal() {
        document.getElementById('addProductModal').classList.remove('is-open');
    }

    let editOriginalCategoryId = '';
    let editOriginalSku = '';

    function openEditProductModal(button) {
        const product = JSON.parse(button.dataset.product);

        editOriginalCategoryId = product.category_id || '';
        editOriginalSku = product.sku || '';

        document.getElementById('edit_id').value = product.id;
        document.getElementById('edit_name').value = product.name;
        document.getElementById('edit_category_id').value = editOriginalCategoryId;
        document.getElementById('edit_price').value = parseInt(product.price, 10) || 0;
        document.getElementById('edit_stock').value = parseInt(product.stock, 10) || 0;
        document.getElementById('edit_image').value = '';

        const imageWrap = document.getElementById('edit_image_preview_wrap');
        const imagePreview = document.getElementById('edit_image_preview');
        if (product.image) {
            imagePreview.src = product.image;
            imageWrap.style.display = 'flex';
        } else {
            imagePreview.src = '';
            imageWrap.style.display = 'none';
        }

        updateEditSkuPreview();
        document.getElementById('editProductModal').classList.add('is-open');
    }

    function closeEditProductModal() {
        document.getElementById('editProductModal').classList.remove('is-open');
    }

    function updateModalSkuPreview() {
        const categorySelect = document.getElementById('modal_category_id');
        const skuPreview = document.getElementById('modal_sku_preview');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const prefix = selectedOption ? selectedOption.dataset.prefix : '';

        skuPreview.value = prefix ? prefix + '-0001+' : 'PRD-0001+';
    }

    document.getElementById('modal_category_id').addEventListener('change', updateModalSkuPreview);
    updateModalSkuPreview();

    function updateEditSkuPreview() {
        const categorySelect = document.getElementById('edit_category_id');
        const skuPreview = document.getElementById('edit_sku_preview');
        const skuNote = document.getElementById('edit_sku_note');
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        const prefix = selectedOption ? selectedOption.dataset.prefix : '';

        if (categorySelect.value === editOriginalCategoryId && editOriginalSku !== '') {
            skuPreview.value = editOriginalSku;
            skuNote.textContent = 'SKU saat ini untuk produk ini.';
            return;
        }

        skuPreview.value = prefix ? prefix + '-0001+' : 'PRD-0001+';
        skuNote.textContent = 'Kategori berubah, SKU akan ikut kategori yang dipilih setelah disimpan.';
    }

    document.getElementById('edit_category_id').addEventListener('change', updateEditSkuPreview);

    function openProductSummary(button) {
        const product = JSON.parse(button.dataset.product);
        const summaryImage = document.getElementById('summaryImage');

        document.getElementById('summaryName').textContent = product.name;
        document.getElementById('summarySku').textContent = product.sku;
        document.getElementById('summaryCategory').textContent = product.category;
        document.getElementById('summaryPrice').textContent = product.price;
        document.getElementById('summaryStock').textContent = product.stock;
        document.getElementById('summaryStatus').textContent = product.status;
        document.getElementById('summaryCreated').textContent = product.created_at;
        document.getElementById('summaryUpdated').textContent = product.updated_at;

        if (product.image) {
            summaryImage.innerHTML = '<img src="' + product.image + '" alt="">';
        } else {
            summaryImage.innerHTML = '<i class="ri-image-line"></i>';
        }

        document.getElementById('productSummaryModal').classList.add('is-open');
    }

    function closeProductSummary() {
        document.getElementById('productSummaryModal').classList.remove('is-open');
    }

    // Reset all filters in modal
    function resetFilters() {
        const form = document.querySelector('#filterModal form');
        form.querySelector('input[name="search"]').value = '';
        form.querySelector('select[name="category_id"]').value = '';
        form.submit();
    }

    // Apply sorting
    function applySorting(sortVal) {
        const url = new URL(<?= json_encode($productUrl(['page_num' => 1])) ?>, window.location.href);
        url.searchParams.set('sort', sortVal);
        window.location.href = url.toString();
    }

    // Change limit (rows per page)
    function changeLimit(limitVal) {
        const url = new URL(<?= json_encode($productUrl(['page_num' => 1])) ?>, window.location.href);
        url.searchParams.set('limit', limitVal);
        window.location.href = url.toString();
    }
</script>

<?php
include 'views/layout/footer.php';
?>
