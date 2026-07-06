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
        $query = "SELECT p.*, c.name AS category_name 
                  FROM {$this->table} p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE 1=1";
        
        $params = [];
        $types = "";

        // Search filter
        if (!empty($filters['search'])) {
            $query .= " AND p.name LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
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
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $query .= " AND name LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
            $types .= "s";
        }

        if (!empty($filters['category_id'])) {
            $query .= " AND category_id = ?";
            $params[] = $filters['category_id'];
            $types .= "i";
        }

        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
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
        $query = "SELECT p.*, c.name AS category_name 
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

    // Create a product
    public function createProduct($data) {
        $status = $this->determineStatus($data['stock']);

        $query = "INSERT INTO {$this->table} (name, category_id, price, stock, image, status) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
            // NOTE: price type diubah dari 'i' (integer) ke 'd' (double) supaya nilai desimal
            // (misalnya Rp 85.000,50) tidak terpotong jadi bilangan bulat.
            $stmt->bind_param(
                "sidiss",
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

        $query = "UPDATE {$this->table} 
                  SET name = ?, category_id = ?, price = ?, stock = ?, image = ?, status = ? 
                  WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $categoryId = !empty($data['category_id']) ? $data['category_id'] : null;
            // NOTE: price type diubah dari 'i' ke 'd' (lihat catatan di createProduct()).
            $stmt->bind_param(
                "sidissi",
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
        $query = "INSERT INTO categories (name) VALUES (?)";
        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $name);
            if ($stmt->execute()) {
                return $this->conn->insert_id;
            }
        }
        return false;
    }

    // Summary stats for products dashboard
    public function getSummaryStats() {
        $stats = [
            'total_products' => 0,
            'low_stock_items' => 0,
            'total_inventory_value' => 0,
            'total_categories' => 0
        ];

        // Total products count
        $result = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table}");
        if ($result) {
            $stats['total_products'] = $result->fetch_assoc()['count'] ?? 0;
        }

        // Low stock count (status = 'low_stock' or stock between 1 and 10)
        $result = $this->conn->query("SELECT COUNT(*) as count FROM {$this->table} WHERE status = 'low_stock'");
        if ($result) {
            $stats['low_stock_items'] = $result->fetch_assoc()['count'] ?? 0;
        }

        // Total inventory value (sum of price * stock)
        $result = $this->conn->query("SELECT SUM(price * stock) as val FROM {$this->table}");
        if ($result) {
            $stats['total_inventory_value'] = $result->fetch_assoc()['val'] ?? 0;
        }

        // Total categories count
        $result = $this->conn->query("SELECT COUNT(*) as count FROM categories");
        if ($result) {
            $stats['total_categories'] = $result->fetch_assoc()['count'] ?? 0;
        }

        return $stats;
    }
}