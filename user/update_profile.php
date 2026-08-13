<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: profile.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["flash_error"] = "Invalid request. Please try again.";
    header("Location: profile.php");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$phone = trim($_POST["phone"] ?? "");

$_SESSION["profile_old"] = [
    "name" => $name,
    "email" => $email,
    "phone" => $phone
];

$userId = (int) $_SESSION["user_id"];

if ($name === "" || mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $_SESSION["flash_error"] = "Name must contain between 2 and 100 characters.";
    header("Location: profile.php");
    exit;
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION["flash_error"] = "Please enter a valid email address.";
    header("Location: profile.php");
    exit;
}

if (mb_strlen($phone) > 20) {
    $_SESSION["flash_error"] = "Phone number is too long.";
    header("Location: profile.php");
    exit;
}

try {

    // Check email is not used by another account
    $checkStatement = $conn->prepare(
        "SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1"
    );

    $checkStatement->execute([
        ":email" => $email,
        ":id" => $userId
    ]);

    if ($checkStatement->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["flash_error"] = "This email is already in use.";
        header("Location: profile.php");
        exit;
    }

    $updateStatement = $conn->prepare(
        "UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id"
    );

    $updateStatement->execute([
        ":name" => $name,
        ":email" => $email,
        ":phone" => $phone !== "" ? $phone : null,
        ":id" => $userId
    ]);

    $_SESSION["user_name"] = $name;

    unset($_SESSION["profile_old"]);

    $_SESSION["flash"] = "Profile updated successfully.";

    header("Location: profile.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["flash_error"] = "Unable to update profile. Please try again.";

    header("Location: profile.php");
    exit;
}