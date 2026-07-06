<?php

// Memanggil semua controller
require_once 'controllers/AuthController.php';
// require_once 'controllers/DashboardController.php';
require_once 'controllers/ProdukController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/EventController.php';

// Mengambil parameter URL
$page   = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Router
switch ($page) {

    case 'dashboard':
        $controller = new DashboardController();
        break;

    case 'products':
        $controller = new ProdukController();
        break;

    case 'orders':
        $controller = new OrderController();
        break;

    case 'users':
        $controller = new UserController();
        break;

    case 'events':
        $controller = new EventController();
        break;

    case 'login':
    case 'register':
        $controller = new AuthController();
        break;

    default:
        die("404 - Page Not Found");
}

// Menjalankan method sesuai action
if (method_exists($controller, $action)) {
    $controller->$action();

} else {
    die("404 - Action Not Found");
}