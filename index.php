<?php

session_start();

require_once 'controllers/AuthController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/ProdukController.php';
require_once 'controllers/OrderController.php';
require_once 'controllers/UserController.php';

$page   = $_GET['page'] ?? 'signin';
$action = $_GET['action'] ?? null;

switch ($page) {

    case 'signin':
        $controller = new AuthController();
        $controller->signin();
        exit;

    case 'signup':
        $controller = new AuthController();
        $controller->signup();
        exit;

    case 'signinProcess':
        $controller = new AuthController();
        $controller->signinProcess();
        exit;

    case 'signupProcess':
        $controller = new AuthController();
        $controller->signupProcess();
        exit;

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

    default:
        die("404 - Page Not Found");
}

// default action untuk controller lain
$action = $action ?? 'index';

if (method_exists($controller, $action)) {
    $controller->$action();
} else {
    die("404 - Action Not Found");
}