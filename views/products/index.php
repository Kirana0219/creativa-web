<?php
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
        <a href="index.php?page=products&action=create" class="btn-add-product">
            <i class="ri-add-line icon-lg"></i>
            <span>Add Product</span>
        </a>
    </div>

    <!-- Stats Row -->
    <div class="stats-grid">
        <!-- Stat Card 1 -->
        <div class="stat-card">
            <div class="stat-icon icon-total">
                <i class="ri-shopping-bag-3-line"></i>
            </div>
            <div class="stat-details">
                <span class="stat-label">Total Products</span>
                <span class="stat-value"><?= number_format($stats['total_products'] ?? 0) ?></span>
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
                    <th class="checkbox-col">
                        <input type="checkbox" class="checkbox-custom" onclick="toggleAllCheckboxes(this)">
                    </th>
                    <th>Product</th>
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
                        <td colspan="7" class="empty-state-cell">
                            <i class="ri-inbox-line empty-state-icon"></i>
                            Belum ada produk. Silakan tambahkan produk baru!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>
                                <input type="checkbox" class="checkbox-custom row-checkbox">
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
                                    <a href="index.php?page=products&action=edit&id=<?= $p['id'] ?>" class="product-name-text">
                                        <?= htmlspecialchars($p['name']) ?>
                                    </a>
                                </div>
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
                                    <a href="index.php?page=products&action=edit&id=<?= $p['id'] ?>" class="btn-action-icon" title="Edit">
                                        <i class="ri-pencil-line"></i>
                                    </a>
                                    <a href="index.php?page=products&action=delete&id=<?= $p['id'] ?>" class="btn-action-icon btn-action-delete" title="Delete" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                        <i class="ri-delete-bin-line"></i>
                                    </a>
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
                    <label>Search Name</label>
                    <input type="text" name="search" class="filter-input" placeholder="Search by product name..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="filter-form-group">
                    <label>Category</label>
                    <select name="category_id" class="filter-input">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
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
    // Toggle checkboxes
    function toggleAllCheckboxes(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
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
        const modal = document.getElementById('filterModal');
        if (event.target == modal) {
            modal.classList.remove('is-open');
        }
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