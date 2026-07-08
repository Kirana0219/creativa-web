<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $currentPage = $_GET['page'] ?? 'dashboard';
    $headerUser = null;

    if (isset($_SESSION['user_id'])) {
        require_once 'models/UserModel.php';
        $userModel = new UserModel();
        $headerUser = $userModel->getUserById((int) $_SESSION['user_id']);
    }

    $profileName = $headerUser['name'] ?? ($_SESSION['name'] ?? 'Admin');
    $profileEmail = $headerUser['email'] ?? ($_SESSION['email'] ?? 'Business Owner');
    $profileAvatar = $headerUser['avatar'] ?? '';
    $profileAvatar = $profileAvatar ? 'assets/uploads/' . $profileAvatar : 'https://i.pravatar.cc/150?u=' . urlencode($profileEmail);
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? "Creativa"; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css" rel="stylesheet">   
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/layout.css">

    <?php if ($currentPage === 'orders'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <link rel="stylesheet" href="assets/css/orders.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <?php endif; ?>
</head>

<body>
    <div class="main-content">
        <div class="top-header">
            <!-- <div class="search-box">
                <i class="ri-search-line"></i>
                <input type="text" placeholder="Search...">
            </div> -->
            <div class="header-breadcrumb">
                <span><?= htmlspecialchars($breadcrumb ?? 'Dashboard'); ?></span>
            </div>

            <div class="header-right">
                <div class="icon-btn">
                    <i class="fa-regular fa-bell"></i>
                </div>

                <div class="vertical-divider"></div>

                <div class="profile">
                    <img src="<?= htmlspecialchars($profileAvatar); ?>" alt="<?= htmlspecialchars($profileName); ?>">
                    <div class="profile-info">
                        <h6><?= htmlspecialchars($profileName); ?></h6>
                        <small><?= htmlspecialchars($profileEmail); ?></small>
                    </div>
                </div>
            </div>
        </div>
