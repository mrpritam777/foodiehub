<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$cartId = filter_input(INPUT_POST, "cart_id", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    header("Location: cart.php");
    exit;
}

if (!$cartId) {
    header("Location: cart.php");
    exit;
}

try {

    $stmt = $conn->prepare(
        "DELETE FROM cart WHERE id = :id AND user_id = :user_id"
    );

    $stmt->execute([
        ":id" => $cartId,
        ":user_id" => (int) $_SESSION["user_id"]
    ]);

    $_SESSION["flash"] = "Item removed from cart.";

    header("Location: cart.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["flash"] = "Unable to remove item.";

    header("Location: cart.php");
    exit;
}