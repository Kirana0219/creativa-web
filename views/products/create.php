<?php
include 'views/layout/header.php';
include 'views/layout/sidebar.php';
?>

<link rel="stylesheet" href="assets/css/products.css">

<div class="form-wrapper">
    <div class="form-header">
        <h1>Add New Product</h1>
        <p>Introduce a new item to your product catalog list.</p>
    </div>

    <div class="form-card">
        <form action="index.php?page=products&action=store" method="POST" enctype="multipart/form-data">

            <div class="form-group full-width">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" class="form-input-custom" required placeholder="e.g. Premium Arabica Coffee">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="category_id">Category</label>
                    <div class="category-select-group">
                        <select id="category_id" name="category_id" class="form-input-custom">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-quick-add" onclick="openCatModal()" title="Add New Category">
                            <i class="ri-add-line"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" class="form-input-custom" accept="image/*">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price (IDR)</label>
                    <input type="number" id="price" name="price" class="form-input-custom" required min="0" placeholder="e.g. 85000">
                </div>

                <div class="form-group">
                    <label for="stock">Initial Stock</label>
                    <input type="number" id="stock" name="stock" class="form-input-custom" required min="0" placeholder="e.g. 100">
                </div>
            </div>

            <div class="form-actions">
                <a href="index.php?page=products" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Add Product</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Category Add Modal -->
<div class="cat-modal-backdrop" id="catModal">
    <div class="cat-modal">
        <div class="filter-modal-header">
            <h5>New Category</h5>
            <button type="button" class="filter-modal-close" onclick="closeCatModal()">&times;</button>
        </div>
        <form action="index.php?page=products&action=addCategory" method="POST">
            <div class="filter-modal-body">
                <div class="filter-form-group tight">
                    <label for="cat_name">Category Name</label>
                    <input type="text" id="cat_name" name="name" class="filter-input" placeholder="e.g. Beverages" required>
                </div>
            </div>
            <div class="filter-modal-footer">
                <button type="button" class="btn-filter-reset" onclick="closeCatModal()">Cancel</button>
                <button type="submit" class="btn-filter-apply">Create</button>
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

    // Modal click-outside close
    window.onclick = function(event) {
        const modal = document.getElementById('catModal');
        if (event.target == modal) {
            modal.classList.remove('is-open');
        }
    }
</script>

<?php
include 'views/layout/footer.php';
?>