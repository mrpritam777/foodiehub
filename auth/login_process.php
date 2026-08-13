<?php

session_start();

require_once __DIR__ . "/../includes/functions.php";
require __DIR__ . "/../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: login.php");
    exit;
}

$email = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

if ($email === "" || $password === "") {
    $_SESSION["error"] = "Please enter your email and password.";
    header("Location: login.php");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
$stmt->execute([":email" => $email]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

// if (!$user || !password_verify($password, $user["password"])) {
//     $_SESSION["error"] = "Invalid email or password.";
//     header("Location: login.php");
//     exit;
// }

session_regenerate_id(true);

if ($user["role"] === "admin") {
    $_SESSION["admin_id"] = $user["id"];
    $_SESSION["admin_name"] = $user["name"];

    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit;
}

$_SESSION["user_id"] = $user["id"];
$_SESSION["user_name"] = $user["name"];

header("Location: " . BASE_URL . "/user/index.php");
exit;