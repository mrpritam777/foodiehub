<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: change_password.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["flash_error"] = "Invalid request. Please try again.";
    header("Location: change_password.php");
    exit;
}

$currentPassword = $_POST["current_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (strlen($newPassword) < 6) {
    $_SESSION["flash_error"] = "New password must be at least 6 characters long.";
    header("Location: change_password.php");
    exit;
}

if ($newPassword !== $confirmPassword) {
    $_SESSION["flash_error"] = "New password and confirmation do not match.";
    header("Location: change_password.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT password FROM users WHERE id = :id LIMIT 1"
);

$stmt->execute([":id" => (int) $_SESSION["user_id"]]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($currentPassword, $user["password"])) {
    $_SESSION["flash_error"] = "Current password is incorrect.";
    header("Location: change_password.php");
    exit;
}

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateStatement = $conn->prepare(
    "UPDATE users SET password = :password WHERE id = :id"
);

$updateStatement->execute([
    ":password" => $hashedPassword,
    ":id" => (int) $_SESSION["user_id"]
]);

$_SESSION["flash"] = "Password changed successfully.";

header("Location: change_password.php");
exit;