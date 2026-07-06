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

    public function getSummaryStats()
    {
        $query = "SELECT
                    COUNT(*) AS total_orders,
                    COALESCE(SUM(status = 'pending'), 0) AS pending_orders,
                    COALESCE(SUM(status = 'shipped'), 0) AS shipped_orders,
                    COALESCE(SUM(status = 'cancelled'), 0) AS cancelled_orders
                FROM orders";

        $result = $this->conn->query($query);
        return $result ? $result->fetch_assoc() : [
            'total_orders' => 0,
            'pending_orders' => 0,
            'shipped_orders' => 0,
            'cancelled_orders' => 0,
        ];
    }
}
