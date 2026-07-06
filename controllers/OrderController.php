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
        $stats = $this->orderModel->getSummaryStats();
        $title = "Orders";
        $breadcrumb = "Dashboard / Orders";

        include 'views/orders/index.php';
    }
}
