<?php

require_once 'config/database.php';

class OrderModel
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getAllOrders()
    {
        $query = "SELECT
                    o.id,
                    o.order_code,
                    o.order_date,
                    o.payment_status,
                    o.status AS order_status,
                    o.total_amount,
                    c.name AS customer_name,
                    c.email,
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
                    o.status,
                    o.total_amount,
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
                    o.status AS order_status,
                    o.total_amount,
                    c.name AS customer_name,
                    c.email,
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
                    o.status,
                    o.total_amount,
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