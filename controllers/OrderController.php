<?php

class OrderController
{
    public function index()
    {
        $title = "Orders";
        $breadcrumb = "Dashboard / Orders";
        include 'views/orders/index.php';
    }
}