<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/functions.php";

$isLoggedIn = isset($_SESSION["user_id"]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-size: 1.4rem;
            font-weight: 700;
        }
        .product-card {
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 .5rem 1rem rgba(0,0,0,.12);
        }
        .product-img {
            height: 180px;
            object-fit: cover;
        }
        .qty-input {
            max-width: 70px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-danger shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="ProductHub" height="40" class="me-2 rounded">
            <!-- ProductHub -->
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center">

                <li class="nav-item">
                    <a class="nav-link" href="index.php">Products</a>
                </li>

                <?php if ($isLoggedIn): ?>

                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fa-solid fa-cart-shopping"></i>
                            Cart
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-user"></i> <?= htmlspecialchars($_SESSION["user_name"]) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-address-card me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="change_password.php"><i class="fa-solid fa-key me-2"></i>Change Password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                        </ul>
                    </li>

                <?php else: ?>

                    <li class="nav-item">
                        <a class="btn btn-outline-light me-2" href="../auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light" href="../auth/register.php">Register</a>
                    </li>

                <?php endif; ?>

            </ul>
        </div>
    </div>
</nav>