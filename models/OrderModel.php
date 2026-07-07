<?php

require_once 'config/database.php';

class OrderModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
        $this->ensureOrderColumns();
    }

    public function getAllOrders()
    {
        $query = "SELECT
                    o.id,
                    o.order_code,
                    o.order_date,
                    o.payment_status,
                    o.payment_method,
                    o.status AS order_status,
                    o.total_items AS stored_total_items,
                    o.total_amount,
                    o.internal_notes,
                    c.name AS customer_name,
                    c.email,
                    COALESCE(NULLIF(o.total_items, 0), SUM(COALESCE(oi.quantity, 0)), 0) AS total_items,
                    GROUP_CONCAT(
                        CONCAT(p.name, IF(oi.quantity > 1, CONCAT(' x', oi.quantity), ''))
                        ORDER BY oi.id
                        SEPARATOR ', '
                    ) AS product,
                    MIN(p.image) AS product_image
                FROM orders o
                INNER JOIN customers c ON c.id = o.customer_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN products p ON p.id = oi.product_id
                GROUP BY
                    o.id,
                    o.order_code,
                    o.order_date,
                    o.payment_status,
                    o.payment_method,
                    o.status,
                    o.total_items,
                    o.total_amount,
                    o.internal_notes,
                    c.name,
                    c.email
                ORDER BY o.order_date DESC, o.id DESC";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Ambil order terbaru saja, dipakai untuk tabel di halaman Dashboard.
     * (Beda dengan getAllOrders() yang mengambil semua data untuk halaman Orders penuh)
     */
    public function getRecentOrders($limit = 10, $offset = 0)
    {
        $query = "SELECT
                    o.id,
                    o.order_code,
                    o.order_date,
                    o.payment_status,
                    o.payment_method,
                    o.status AS order_status,
                    o.total_items AS stored_total_items,
                    o.total_amount,
                    o.internal_notes,
                    c.name AS customer_name,
                    c.email,
                    COALESCE(NULLIF(o.total_items, 0), SUM(COALESCE(oi.quantity, 0)), 0) AS total_items,
                    GROUP_CONCAT(
                        CONCAT(p.name, IF(oi.quantity > 1, CONCAT(' x', oi.quantity), ''))
                        ORDER BY oi.id
                        SEPARATOR ', '
                    ) AS product,
                    MIN(p.image) AS product_image
                FROM orders o
                INNER JOIN customers c ON c.id = o.customer_id
                LEFT JOIN order_items oi ON oi.order_id = o.id
                LEFT JOIN products p ON p.id = oi.product_id
                GROUP BY
                    o.id,
                    o.order_code,
                    o.order_date,
                    o.payment_status,
                    o.payment_method,
                    o.status,
                    o.total_items,
                    o.total_amount,
                    o.internal_notes,
                    c.name,
                    c.email
                ORDER BY o.order_date DESC, o.id DESC
                LIMIT ? OFFSET ?";

        $stmt = $this->conn->prepare($query);
        if ($stmt) {
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    public function createOrder($data)
    {
        if ($data['customer_name'] === '' || $data['email'] === '') {
            return false;
        }

        $product = $this->getProductForOrder((int) ($data['product_id'] ?? 0));
        if (!$product) {
            return false;
        }

        $customerId = $this->findOrCreateCustomer($data['customer_name'], $data['email']);
        if (!$customerId) {
            return false;
        }

        $orderCode = $this->generateOrderCode();
        $orderDate = $data['order_date'] ?: date('Y-m-d');
        $totalItems = max(1, (int) $data['total_items']);
        $totalAmount = (float) $product['price'] * $totalItems;

        $this->conn->begin_transaction();
        $query = "INSERT INTO orders
                    (order_code, customer_id, order_date, payment_status, payment_method, status, total_items, total_amount, internal_notes)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            $this->conn->rollback();
            return false;
        }

        $paymentStatus = $data['payment_status'];
        $paymentMethod = $data['payment_method'];
        $orderStatus = $data['order_status'];
        $internalNotes = $data['internal_notes'];

        $stmt->bind_param(
            "sissssids",
            $orderCode,
            $customerId,
            $orderDate,
            $paymentStatus,
            $paymentMethod,
            $orderStatus,
            $totalItems,
            $totalAmount,
            $internalNotes
        );

        if (!$stmt->execute()) {
            $this->conn->rollback();
            return false;
        }

        $orderId = (int) $this->conn->insert_id;
        $productId = (int) $product['id'];
        $price = (float) $product['price'];
        $itemStmt = $this->conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");

        if (!$itemStmt) {
            $this->conn->rollback();
            return false;
        }

        $itemStmt->bind_param("iiid", $orderId, $productId, $totalItems, $price);
        if (!$itemStmt->execute()) {
            $this->conn->rollback();
            return false;
        }

        $this->conn->commit();
        return true;
    }

    public function updateOrder($id, $data)
    {
        $order = $this->getOrderById($id);
        if (!$order) {
            return false;
        }

        if ($data['customer_name'] !== '' && $data['email'] !== '') {
            $customerUpdated = $this->updateCustomer(
                (int) $order['customer_id'],
                $data['customer_name'],
                $data['email']
            );

            if (!$customerUpdated) {
                return false;
            }
        }

        $orderDate = $data['order_date'] ?: $order['order_date'];
        $query = "UPDATE orders
                  SET order_date = ?,
                      payment_status = ?,
                      payment_method = ?,
                      status = ?,
                      total_items = ?,
                      total_amount = ?,
                      internal_notes = ?
                  WHERE id = ?";

        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return false;
        }

        $paymentStatus = $data['payment_status'];
        $paymentMethod = $data['payment_method'];
        $orderStatus = $data['order_status'];
        $totalItems = $data['total_items'];
        $totalAmount = $data['total_amount'];
        $internalNotes = $data['internal_notes'];

        $stmt->bind_param(
            "ssssidsi",
            $orderDate,
            $paymentStatus,
            $paymentMethod,
            $orderStatus,
            $totalItems,
            $totalAmount,
            $internalNotes,
            $id
        );

        return $stmt->execute();
    }

    public function deleteOrder($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM orders WHERE id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getProductsForOrder()
    {
        $query = "SELECT id, name, price, stock, status
                  FROM products
                  WHERE status != 'out_of_stock'
                  ORDER BY name ASC";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getProductForOrder($id)
    {
        $stmt = $this->conn->prepare("SELECT id, name, price FROM products WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    private function getOrderById($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    private function findOrCreateCustomer($name, $email)
    {
        $stmt = $this->conn->prepare("SELECT id FROM customers WHERE email = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result ? $result->fetch_assoc() : null;

        if ($existing) {
            $this->updateCustomer((int) $existing['id'], $name, $email);
            return (int) $existing['id'];
        }

        $insert = $this->conn->prepare("INSERT INTO customers (name, email) VALUES (?, ?)");
        if (!$insert) {
            return null;
        }

        $insert->bind_param("ss", $name, $email);
        if (!$insert->execute()) {
            return null;
        }

        return (int) $insert->insert_id;
    }

    private function updateCustomer($id, $name, $email)
    {
        $stmt = $this->conn->prepare("UPDATE customers SET name = ?, email = ? WHERE id = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("ssi", $name, $email, $id);
        return $stmt->execute();
    }

    private function generateOrderCode()
    {
        do {
            $code = 'ORD-' . random_int(1000, 9999);
            $stmt = $this->conn->prepare("SELECT id FROM orders WHERE order_code = ? LIMIT 1");
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $exists = $stmt->get_result()->fetch_assoc();
        } while ($exists);

        return $code;
    }

    private function ensureOrderColumns()
    {
        $columns = [
            'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(100) DEFAULT NULL AFTER payment_status",
            'total_items' => "ALTER TABLE orders ADD COLUMN total_items INT NOT NULL DEFAULT 0 AFTER status",
            'internal_notes' => "ALTER TABLE orders ADD COLUMN internal_notes TEXT DEFAULT NULL AFTER total_amount",
        ];

        foreach ($columns as $column => $alterSql) {
            $result = $this->conn->query("SHOW COLUMNS FROM orders LIKE '{$column}'");
            if ($result && $result->num_rows === 0) {
                $this->conn->query($alterSql);
            }
        }
    }

    /**
     * Total keseluruhan order (untuk pagination di dashboard).
     */
    public function countAllOrders()
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM orders");
        if ($result) {
            return (int) ($result->fetch_assoc()['total'] ?? 0);
        }
        return 0;
    }

    /**
     * Statistik dasar (dipakai di halaman Orders).
     */
    public function getSummaryStats()
    {
        $query = "SELECT
                    COUNT(*) AS total_orders,
                    COALESCE(SUM(total_amount), 0) AS total_sales,
                    COALESCE(SUM(status = 'pending'), 0) AS pending_orders,
                    COALESCE(SUM(status = 'shipped'), 0) AS shipped_orders,
                    COALESCE(SUM(status = 'cancelled'), 0) AS cancelled_orders
                FROM orders";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_assoc() : [
            'total_orders' => 0,
            'total_sales' => 0,
            'pending_orders' => 0,
            'shipped_orders' => 0,
            'cancelled_orders' => 0,
        ];
    }

    /**
     * Statistik untuk Dashboard: Total Sales & Total Orders dalam periode tertentu,
     * plus persentase pertumbuhan (growth) dibanding periode sebelumnya dengan panjang yang sama.
     *
     * Total Sales dihitung dari SUM(total_amount) seluruh order pada periode tsb —
     * artinya sales = penjumlahan nilai (amount) dari setiap order, bukan dikalikan.
     *
     * @param int $days Panjang periode dalam hari (default 7 = "Last 7 Days")
     */
    public function getDashboardStats($days = 7)
    {
        // ==== Periode saat ini ====
        $currentQuery = "SELECT
                            COUNT(*) AS total_orders,
                            COALESCE(SUM(total_amount), 0) AS total_sales
                          FROM orders
                          WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        $stmt = $this->conn->prepare($currentQuery);
        $stmt->bind_param("i", $days);
        $stmt->execute();
        $current = $stmt->get_result()->fetch_assoc();

        // ==== Periode sebelumnya (panjang sama) untuk dibandingkan ====
        $doubleDays = $days * 2;
        $previousQuery = "SELECT
                            COUNT(*) AS total_orders,
                            COALESCE(SUM(total_amount), 0) AS total_sales
                          FROM orders
                          WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                            AND order_date < DATE_SUB(CURDATE(), INTERVAL ? DAY)";
        $stmt2 = $this->conn->prepare($previousQuery);
        $stmt2->bind_param("ii", $doubleDays, $days);
        $stmt2->execute();
        $previous = $stmt2->get_result()->fetch_assoc();

        return [
            'total_sales'    => (float) $current['total_sales'],
            'total_orders'   => (int) $current['total_orders'],
            'sales_growth'   => $this->calculateGrowth($current['total_sales'], $previous['total_sales']),
            'orders_growth'  => $this->calculateGrowth($current['total_orders'], $previous['total_orders']),
        ];
    }

    /**
     * Hitung persentase pertumbuhan antara nilai sekarang vs sebelumnya.
     */
    private function calculateGrowth($current, $previous)
    {
        $current = (float) $current;
        $previous = (float) $previous;

        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
