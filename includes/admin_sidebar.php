<?php
if (!defined("BASE_URL")) {
    require_once __DIR__ . "/../config/database.php";
}

$activePage = $activePage ?? "";
$base = BASE_URL;
?>

<div class="sidebar position-fixed">

    <div class="text-center py-4">

        <a href="<?= $base ?>/admin/dashboard.php">
            <img src="<?= $base ?>/assets/images/logo.svg" alt="ProductHub" class="sidebar-logo">
        </a>

    </div>

    <a href="<?= $base ?>/admin/dashboard.php" class="<?= $activePage === "dashboard" ? "active" : "" ?>">
        <i class="fa fa-gauge"></i>
        Dashboard
    </a>

    <a href="<?= $base ?>/admin/categories/index.php" class="<?= $activePage === "categories" ? "active" : "" ?>">
        <i class="fa fa-list"></i>
        Categories
    </a>

    <a href="<?= $base ?>/admin/products/index.php" class="<?= $activePage === "products" ? "active" : "" ?>">
        <i class="fa fa-box"></i>
        Products
    </a>

    <a href="<?= $base ?>/admin/orders/index.php" class="<?= $activePage === "orders" ? "active" : "" ?>">
        <i class="fa fa-cart-shopping"></i>
        Orders
    </a>

    <a href="<?= $base ?>/admin/users/index.php" class="<?= $activePage === "users" ? "active" : "" ?>">
        <i class="fa fa-users"></i>
        Users
    </a>

    <a href="<?= $base ?>/admin/reports/index.php" class="<?= $activePage === "reports" ? "active" : "" ?>">
        <i class="fa fa-chart-line"></i>
        Reports
    </a>

    <a href="<?= $base ?>/auth/logout.php">
        <i class="fa fa-right-from-bracket"></i>
        Logout
    </a>

</div>

<style>
    .sidebar a.active {
        background-color: #0d6efd;
        color: #fff;
    }
</style>