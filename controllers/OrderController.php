<?php

require_once 'models/OrderModel.php';

class OrderController
{
    private $orderModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
    }

    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=signin');
            exit;
        }
    }

    public function index()
    {
        $this->checkAuth();

        $orders = $this->orderModel->getAllOrders();
        $products = $this->orderModel->getProductsForOrder();
        $stats = $this->orderModel->getSummaryStats();
        $title = "Orders";
        $breadcrumb = "Dashboard > Orders";

        include 'views/orders/index.php';
    }

    public function store()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=orders');
            exit;
        }

        $success = $this->orderModel->createOrder($this->orderDataFromRequest());
        $_SESSION['order_flash'] = $success
            ? ['type' => 'success', 'message' => 'Order berhasil ditambahkan.']
            : ['type' => 'danger', 'message' => 'Order gagal ditambahkan.'];

        header('Location: index.php?page=orders');
        exit;
    }

    public function update()
    {
        $this->checkAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?page=orders');
            exit;
        }

        $id = (int) ($_POST['order_id'] ?? 0);
        $success = $id > 0 && $this->orderModel->updateOrder($id, $this->orderDataFromRequest());
        $_SESSION['order_flash'] = $success
            ? ['type' => 'success', 'message' => 'Order berhasil diperbarui.']
            : ['type' => 'danger', 'message' => 'Order gagal diperbarui.'];

        header('Location: index.php?page=orders');
        exit;
    }

    public function delete()
    {
        $this->checkAuth();

        $id = (int) ($_GET['id'] ?? 0);
        $success = $id > 0 && $this->orderModel->deleteOrder($id);
        $_SESSION['order_flash'] = $success
            ? ['type' => 'success', 'message' => 'Order berhasil dihapus.']
            : ['type' => 'danger', 'message' => 'Order gagal dihapus.'];

        header('Location: index.php?page=orders');
        exit;
    }

    private function orderDataFromRequest()
    {
        $validOrderStatuses = ['pending', 'shipped', 'delivered', 'cancelled'];
        $validPaymentStatuses = ['pending', 'paid', 'failed'];

        $orderStatus = strtolower(trim($_POST['order_status'] ?? 'pending'));
        $paymentStatus = strtolower(trim($_POST['payment_status'] ?? 'pending'));

        if (!in_array($orderStatus, $validOrderStatuses, true)) {
            $orderStatus = 'pending';
        }

        if (!in_array($paymentStatus, $validPaymentStatuses, true)) {
            $paymentStatus = 'pending';
        }

        $orderDate = trim($_POST['order_date'] ?? date('Y-m-d'));
        if (!$this->isValidDate($orderDate)) {
            $orderDate = date('Y-m-d');
        }

        return [
            'customer_name' => trim($_POST['customer_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'order_date' => $orderDate,
            'order_status' => $orderStatus,
            'payment_status' => $paymentStatus,
            'payment_method' => trim($_POST['payment_method'] ?? ''),
            'product_id' => (int) ($_POST['product_id'] ?? 0),
            'total_items' => max(0, (int) ($_POST['total_items'] ?? 0)),
            'total_amount' => max(0, (float) ($_POST['total_amount'] ?? 0)),
            'internal_notes' => trim($_POST['internal_notes'] ?? ''),
        ];
    }

    private function isValidDate($date)
    {
        $parsed = DateTime::createFromFormat('Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
