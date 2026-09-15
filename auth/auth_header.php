<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../includes/functions.php";

// Authed users should not see the auth pages
if (isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "/user/index.php");
    exit;
}

if (isset($_SESSION["admin_id"])) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? "ProductHub") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #a52834 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-card {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 1rem 2.5rem rgba(0,0,0,.3);
        }
        .auth-logo {
            font-size: 1.6rem;
            font-weight: 700;
        }
        .brand-badge {
            width: 68px;
            height: 68px;
            font-size: 2.2rem;
            background-color: #fff3f3;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>

<div class="container">

    <div style="min-height:100vh;" class="d-flex flex-column justify-content-center py-5">

        <div class="row justify-content-center">

            <div class="col-md-5 col-lg-4">

                <div class="text-center mb-4">

                    <img src="<?= BASE_URL ?>/assets/images/logo.svg" alt="ProductHub" height="70" class="mx-auto mb-3">

                    <h2 class="text-white auth-logo">ProductHub</h2>

                    <p class="text-white-50 mb-0">Order your favorite products online</p>

                </div>

                <div class="card auth-card">

                    <div class="card-body p-4">

                        <h4 class="mb-1"><?= htmlspecialchars($cardTitle ?? "") ?></h4>

                        <p class="text-muted mb-4"><?= htmlspecialchars($cardSubtitle ?? "") ?></p>

                        <?php if (!empty($_SESSION["error"])): ?>

                            <div class="alert alert-danger alert-dismissible fade show py-2">

                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                <?= htmlspecialchars($_SESSION["error"]) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                            </div>

                        <?php endif; ?>

                        <?php if (!empty($_SESSION["success"])): ?>

                            <div class="alert alert-success alert-dismissible fade show py-2">

                                <i class="fa-solid fa-circle-check me-1"></i>
                                <?= htmlspecialchars($_SESSION["success"]) ?>

                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                            </div>

                        <?php endif; ?>

                        <?php unset($_SESSION["error"], $_SESSION["success"]); ?>