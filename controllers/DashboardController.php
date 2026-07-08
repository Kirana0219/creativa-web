<?php

require_once 'models/OrderModel.php';
require_once 'models/ProdukModel.php';

class DashboardController
{
    private $orderModel;
    private $produkModel;

    public function __construct()
    {
        $this->orderModel = new OrderModel();
        $this->produkModel = new ProdukModel();
    }

    public function index()
    {
        // Halaman & rows per page untuk tabel order di dashboard
        $page = isset($_GET['page_num']) ? max(1, (int) $_GET['page_num']) : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 10;
        $offset = ($page - 1) * $limit;

        // Periode growth (default 7 hari, bisa ganti via ?range=30 dsb)
        $rangeDays = isset($_GET['range']) ? (int) $_GET['range'] : 7;

        // ==== Ambil data dari OrderModel ====
        $orderStats = $this->orderModel->getDashboardStats($rangeDays);
        $orders = $this->orderModel->getRecentOrders($limit, $offset);
        $totalOrdersAll = $this->orderModel->countAllOrders();

        // ==== Ambil data dari ProdukModel ====
        $activeProducts = $this->produkModel->getActiveProductsCount();
        $lowStockCount = $this->produkModel->getLowStockCount();

        // ==== Gabungkan jadi satu array $stats untuk view ====
        $stats = [
            'total_sales'      => $orderStats['total_sales'],
            'sales_growth'     => $orderStats['sales_growth'],
            'total_orders'     => $orderStats['total_orders'],
            'orders_growth'    => $orderStats['orders_growth'],
            'active_products'  => $activeProducts,
            'low_stock_alerts' => $lowStockCount,
        ];

        $totalPages = $limit > 0 ? (int) ceil($totalOrdersAll / $limit) : 1;

        require 'views/dashboard/index.php';
    }
}