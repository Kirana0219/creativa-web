<?php

class UserController
{
    public function index()
    {
        include 'views/layout/header.php';
        include 'views/layout/sidebar.php';
        include 'views/users/index.php';
        include 'views/layout/footer.php';
    }
}