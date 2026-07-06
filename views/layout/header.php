<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? "Creativa"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">
    <style>
        <?= file_get_contents('assets/css/layout.css') ?>
    </style>
</head>

<body>
    <div class="main-content">
    <div class="top-header">

        <!-- <div class="page-title">
            <h3><?= $title ?? "Dashboard"; ?></h3>
            <small><?= $breadcrumb ?? "Dashboard"; ?></small>
        </div> -->

        <div class="search-box">
            <i class="ri-search-line"></i>
            <input
                type="text"
                placeholder="Search..."
            >
        </div>

        <div class="header-right">
            <div class="icon-btn">
                <i class="fa-regular fa-bell"></i>
            </div>

            <div class="vertical-divider"></div>

            <div class="profile">
                <img src="https://i.pravatar.cc/150?img=12">
                <div class="profile-info">
                    <h6>Admin</h6>
                    <small>Business Owner</small>
                </div>
            </div>
        </div>

    </div>
