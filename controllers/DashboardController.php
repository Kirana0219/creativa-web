<?php

class DashboardController
{
    public function index() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: index.php?page=signin');
            exit;
        }

        require 'views/dashboard/index.php';
    }
}
?>