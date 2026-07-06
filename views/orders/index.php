<?php
include 'views/layout/header.php';
include 'views/layout/sidebar.php';
?>

<div class="content">
    <div class="orders-container">

        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Orders</h2>
                <p>Manage and track your business transactions</p>
            </div>

            <button class="btn-export">
                <i class="ri-download-line"></i>
                Export Report
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">

        <!-- Card 1 -->
        <div class="summary-card">
            <div class="card-top">
                <div class="icon purple">
                    <i class="ri-file-list-3-line"></i>
                </div>
                <span class="badge success">
                    +12.5%
                </span>
            </div>

            <span class="card-title">
                TOTAL ORDERS
            </span>

            <h3>1,284</h3>
        </div>


        <!-- Card 2 -->
        <div class="summary-card">

            <div class="card-top">
                <div class="icon orange">
                    <i class="ri-time-line"></i>
                </div>
                <span class="badge warning">
                    Active
                </span>
            </div>

            <span class="card-title">
                PENDING
            </span>

            <h3>45</h3>
        </div>

        <!-- Card 3 -->
        <div class="summary-card">

            <div class="card-top">
                <div class="icon green">
                    <i class="ri-truck-line"></i>
                </div>
                <span class="badge success">
                    82%
                </span>
            </div>

            <span class="card-title">
                SHIPPED
            </span>

            <h3>912</h3>
        </div>

        <!-- Card 4 -->
        <div class="summary-card">

            <div class="card-top">
                <div class="icon red">
                    <i class="ri-close-circle-line"></i>
                </div>
                <span class="badge danger">
                    -2.4%
                </span>
            </div>

            <span class="card-title">
                CANCELLED
            </span>

            <h3>12</h3>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="filter-section">
        
        <div class="search-order">
            <i class="ri-search-line"></i>
            <input
                type="text"
                placeholder="Search order ID, customer, or product..."
            >
        </div>

    <div class="filter-group">

        <div class="dropdown">
            <button
                class="custom-filter dropdown-toggle"
                data-bs-toggle="dropdown">
                Status: All
            </button>

            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#">All</a></li>
                <li><a class="dropdown-item" href="#">Pending</a></li>
                <li><a class="dropdown-item" href="#">Packing</a></li>
                <li><a class="dropdown-item" href="#">Shipped</a></li>
                <li><a class="dropdown-item" href="#">Completed</a></li>
                <li><a class="dropdown-item" href="#">Cancelled</a></li>
            </ul>
        </div>

            <div class="date-filter">
                <i class="ri-calendar-line"></i>
                <input
                    id="dateRange"
                    type="text"
                    placeholder="Select Date"
                    readonly
                >
            </div>

            <button class="filter-btn">
                <i class="ri-filter-3-line"></i>
                More Filters
            </button>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="orders-table">

        <!-- Header -->
        <div class="table-header">
            <div>ORDER ID</div>
            <div>CUSTOMER</div>
            <div>DATE</div>
            <div>PRODUCT</div>
            <div>AMOUNT</div>
            <div>PAYMENT</div>
            <div>STATUS</div>
            <div>ACTION</div>
        </div>

        <!-- Row -->
        <!-- <?php while($order = mysqli_fetch_assoc($orders)): ?>
        <div class="table-row">

            <div class="order-id">
                <a href="#">
                    #ORD-<?= $order['id']; ?>
                </a>
            </div>

            <div class="customer">
                <strong>
                    <?= htmlspecialchars($order['customer_name']); ?>
                </strong>
                <span>
                    <?= htmlspecialchars($order['email']); ?>
                </span>
            </div>

            <div>
                <?= date('M d, Y', strtotime($order['order_date'])); ?>
            </div>

            <div class="product">
                <img src="<?= $order['product_image']; ?>">
                <span>
                    <?= htmlspecialchars($order['product']); ?>
                </span>
            </div>

            <div>
                Rp <?= number_format($order['amount'],0,',','.'); ?>
            </div>

            <div>
                <span class="badge-paid">
                    <?= $order['payment_status']; ?>
                </span>
            </div>

            <div>
                <span class="badge-status">
                    <?= $order['order_status']; ?>
                </span>

            </div>

            <div class="actions">
                ...
            </div>
        </div>

        <?php endwhile; ?> -->

        <!-- Orders Footer -->
        <div class="table-footer">
            <span>
                Showing 1 to 10 of 1,284 orders
            </span>

            <div class="pagination">
                <button>Previous</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <span>...</span>
                <button>128</button>
                <button>Next</button>
            </div>
        </div>
    </div>
</div>

<?php
include 'views/layout/footer.php';
?>