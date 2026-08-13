<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/functions.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>FoodieHub Admin</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background-color: #f5f7fb;
        }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background-color: #212529;
            z-index: 1000;
        }

        .sidebar .sidebar-logo {
            display: block;
            max-width: 150px;
            margin: 0 auto;
            padding: 10px 0;
        }

        .sidebar a {
            display: block;
            padding: 14px 20px;
            color: #ffffff;
            text-decoration: none;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #0d6efd;
        }

        .sidebar a i {
            width: 25px;
        }

        .main-content {
            margin-left: 260px;
            min-height: 100vh;
        }

        .card {
            border: 0;
            border-radius: 15px;
        }

        @media (max-width: 991px) {
            .sidebar {
                position: relative !important;
                width: 100%;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>