<div class="sidebar">

    <!-- Logo -->
    <div class="logo">
        <a href="index.php?page=dashboard">
             <img src="assets/images/LOGO.png" alt="Creativa Logo" class="logo-icon">
             <img src="assets/images/REATIVA.png" alt="Creativa Logo" class="logo-text">
            <!-- <span>CREATIVA</span> -->
        </a>
    </div>

    <!-- Menu -->
    <ul class="sidebar-menu">

        <li>
            <a href="index.php?page=dashboard"
               class="<?= ($_GET['page'] ?? 'dashboard') == 'dashboard' ? 'active' : '' ?>">
                <i class="ri-dashboard-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li>
            <a href="index.php?page=products"
               class="<?= ($_GET['page'] ?? '') == 'products' ? 'active' : '' ?>">
                <i class="ri-store-2-line"></i>
                <span>Products</span>
            </a>
        </li>

        <li>
            <a href="index.php?page=orders"
               class="<?= ($_GET['page'] ?? '') == 'orders' ? 'active' : '' ?>">
                <i class="ri-shopping-cart-line"></i>
                <span>Orders</span>
            </a>
        </li>
        
        <li>
            <a href="index.php?page=users"
               class="<?= ($_GET['page'] ?? '') == 'users' ? 'active' : '' ?>">
                <i class="ri-group-line"></i>
                <span>Users</span>
            </a>
        </li>


    </ul>

    <!-- Bottom Menu -->
    <div class="sidebar-bottom">

        <a href="#">
            <i class="ri-settings-3-line"></i>
            <span>Settings</span>
        </a>

        <a href="index.php?page=logout" class="logout">
            <i class="ri-logout-box-r-line"></i>
            <span>Logout</span>
        </a>

    </div>

</div>