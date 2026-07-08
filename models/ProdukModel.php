<?php

require_once 'config/database.php';

class ProdukModel {
    private $conn;
    private $table = 'products';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get all products with filters, sorting, and pagination
    public function getAllProducts($filters = [], $sort = 'newest', $limit = 10, $offset = 0) {
        $query = "SELECT p.*, c.name AS category_name, c.sku_prefix AS category_sku_prefix 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        
        $params = [];
        $types = "";

        // Search filter
        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ? OR c.sku_prefix LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= "i";
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        // Sorting
        switch ($sort) {
            case 'price_asc':
                $query .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $query .= " ORDER BY p.price DESC";
                break;
            case 'oldest':
                $query .= " ORDER BY p.created_at ASC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY p.created_at DESC";
                break;
        }

        // Pagination
        $query .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Count products for pagination
    public function countProducts($filters = []) {
        $query = "SELECT COUNT(*) as total
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $query .= " AND (p.name LIKE ? OR p.sku LIKE ? OR c.name LIKE ? OR c.sku_prefix LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ssss";
        }

        if (!empty($filters['category_id'])) {
            $query .= " AND p.category_id = ?";
            $params[] = $filters['category_id'];
            $types .= "i";
        }

        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $filters['status'];
            $types .= "s";
        }

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            return $row['total'] ?? 0;
        }
        return 0;
    }

    // Get single product by ID
    public function getProductById($id) {
        $query = "SELECT p.*, c.name AS category_name, c.sku_prefix AS category_sku_prefix 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            return $stmt->get_result()->fetch_assoc();
        }
        return null;
    }

    // Helper to determine status based on stock
    private function determineStatus($stock) {
        if ($stock <= 0) {
            return 'out_of_stock';
        } elseif ($stock <= 10) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }

    private function buildSkuPrefix($categoryId, $categoryName = '') {
        $prefix = '';

        if (!empty($categoryId)) {
            $query = "SELECT sku_prefix, name FROM categories WHERE id = ? LIMIT 1";
            $stmt = $this->conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $categoryId);
                $stmt->execute();
                $category = $stmt->get_result()->fetch_assoc();
                if ($category) {
                    $prefix = $category['sku_prefix'] ?: $category['name'];
                }
            }
        }

        if ($prefix === '') {
            $prefix = $categoryName ?: 'PRD';
        }

        $prefix = preg_replace('/[^A-Z0-9]/', '', strtoupper($prefix));
        return substr($prefix ?: 'PRD', 0, 15);
    }

    private function generateUniqueSku($categoryId, $excludeProductId = 0) {
        $prefix = $this->buildSkuPrefix($categoryId);
        $like = $prefix . '-%';
        $excludeProductId = (int) $excludeProductId;
        $nextNumber = 1;

        $query = "SELECT sku
                  FROM {$this->table}
                  WHERE sku LIKE ? AND id <> ?
                  ORDER BY CAST(SUBSTRING_INDEX(sku, '-', -1) AS UNSIGNED) DESC
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);

        if ($stmt) {
            $stmt->bind_param("si", $like, $excludeProductId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            if (!empty($row['sku']) && preg_match('/-(\d+)$/', $row['sku'], $matches)) {
                $nextNumber = ((int) $matches[1]) + 1;
            }
        }

        do {
            $sku = $prefix . '-' . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while ($this->skuExists($sku, $excludeProductId));

        return $sku;
    }

    private function skuExists($sku, $excludeProductId = 0) {
        $query = "SELECT id FROM {$this->table} WHERE sku = ? AND id <> ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("si", $sku, $excludeProductId);
            $stmt->execute();
            return (bool) $stmt->get_result()->fetch_assoc();
        }
        return false;
    }

    // Create a product
    public function createProduct($data) {
        $status = $this->determineStatus($data['stock']);
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $sku = $this->generateUniqueSku($categoryId);

        $query = "INSERT INTO {$this->table} (sku, name, category_id, price, stock, image, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param(
                "ssidiss",
                $sku,
                $data['name'],
                $categoryId,
                $data['price'],
                $data['stock'],
                $data['image'],
                $status
            );
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
        }
        return false;
    }

    // Update a product
    public function updateProduct($id, $data) {
        $status = $this->determineStatus($data['stock']);
        $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
        $currentProduct = $this->getProductById($id);
        $sku = $currentProduct['sku'] ?? null;

        if (empty($sku) || (int) ($currentProduct['category_id'] ?? 0) !== (int) ($categoryId ?? 0)) {
            $sku = $this->generateUniqueSku($categoryId, $id);
        }

        $query = "UPDATE {$this->table} 
                  SET sku = ?, name = ?, category_id = ?, price = ?, stock = ?, image = ?, status = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param(
                "ssidissi",
                $sku,
                $data['name'],
                $categoryId,
                $data['price'],
                $data['stock'],
                $data['image'],
                $status,
                $id
            );
            return $stmt->execute();
        }
        return false;
    }

    // Delete a product
    public function deleteProduct($id) {
        $query = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("i", $id);
            return $stmt->execute();
        }
        return false;
    }

    // Categories Methods
    public function getAllCategories() {
        $query = "SELECT * FROM categories ORDER BY name ASC";
        $result = $this->conn->query($query);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function createCategory($name) {
        $skuPrefix = $this->buildSkuPrefix(null, $name);
        $basePrefix = $skuPrefix;
        $suffix = 2;

        while ($this->categoryPrefixExists($skuPrefix)) {
            $skuPrefix = substr($basePrefix, 0, 12) . $suffix;
            $suffix++;
        }

        $query = "INSERT INTO categories (sku_prefix, name) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ss", $skuPrefix, $name);
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
        }
        return false;
    }

    private function categoryPrefixExists($skuPrefix) {
        $query = "SELECT id FROM categories WHERE sku_prefix = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $skuPrefix);
            $stmt->execute();
            return (bool) $stmt->get_result()->fetch_assoc();
        }
        return false;
    }

    // Summary stats for products dashboard (halaman Products)
    public function getSummaryStats() {
        $stats = [
            'total_products' => 0,
            'low_stock_items' => 0,
            'total_inventory_value' => 0,
            'total_categories' => 0
        ];

        $result = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table}");
        if ($result) {
            $stats['total_products'] = $result->fetch_assoc()['count'] ?? 0;
        }

        $result = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'low_stock'");
        if ($result) {
            $stats['low_stock_items'] = $result->fetch_assoc()['count'] ?? 0;
        }

        $result = $this->conn->query("SELECT SUM(price * stock) as val FROM {$this->table}");
        if ($result) {
            $stats['total_inventory_value'] = $result->fetch_assoc()['val'] ?? 0;
        }

        $result = $this->conn->query("SELECT COUNT(*) as count FROM categories");
        if ($result) {
            $stats['total_categories'] = $result->fetch_assoc()['count'] ?? 0;
        }

        return $stats;
    }

    /**
     * Jumlah produk "aktif" (status bukan out_of_stock) — dipakai untuk stat card
     * "Active Products" di halaman Dashboard.
     */
    public function getActiveProductsCount() {
        $result = $this->conn->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status != 'out_of_stock'"
        );
        if ($result) {
            return (int) ($result->fetch_assoc()['count'] ?? 0);
        }
        return 0;
    }

    /**
     * Jumlah produk dengan stok menipis — dipakai untuk stat card
     * "Low Stock Alerts" di halaman Dashboard.
     */
    public function getLowStockCount() {
        $result = $this->conn->query(
            "SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'low_stock'"
        );
        if ($result) {
            return (int) ($result->fetch_assoc()['count'] ?? 0);
        }
        return 0;
    }

    public function decreaseStock($productId, $qty)
    {
        $sql = "UPDATE products
                SET stock = stock - ?
                WHERE id = ?
                AND stock >= ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $qty, $productId, $qty);

        return $stmt->execute();
    }
    public function increaseStock($productId, $qty)
    {
    $sql = "UPDATE products
            SET stock = stock + ?
            WHERE id = ?";

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param("ii", $qty, $productId);

    return $stmt->execute();
    }
}
