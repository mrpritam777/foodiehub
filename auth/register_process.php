<?php

require_once __DIR__ . "/../includes/functions.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: register.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    header("Location: register.php");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$password = $_POST["password"] ?? "";

$_SESSION["old_name"] = $name;
$_SESSION["old_email"] = $email;
$_SESSION["old_phone"] = $phone;

if ($name === "" || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $_SESSION["error"] = "Name must contain between 2 and 100 characters.";
    header("Location: register.php");
    exit;
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["error"] = "Please enter a valid email address.";
    header("Location: register.php");
    exit;
}

if (mb_strlen($phone) > 12) {
    $_SESSION["error"] = "Phone number is too long.";
    header("Location: register.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION["error"] = "Password must be at least 6 characters long.";
    header("Location: register.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

$hash = password_hash($password, PASSWORD_DEFAULT);

try {

    $check = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $check->execute([":email" => $email]);

    if ($check->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["error"] = "This email is already registered.";
        header("Location: register.php");
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (name, email, phone, password, role)
         VALUES (:name, :email, :phone, :password, 'user')"
    );

    $stmt->execute([
        ":name" => $name,
        ":email" => $email,
        ":phone" => $phone !== "" ? $phone : null,
        ":password" => $hash
    ]);

    unset(
        $_SESSION["old_name"],
        $_SESSION["old_email"],
        $_SESSION["old_phone"]
    );

    $_SESSION["success"] = "Account created successfully. Please login.";

    header("Location: login.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to create account. Please try again.";

    header("Location: register.php");
    exit;
}