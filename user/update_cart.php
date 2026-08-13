<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$cartId = filter_input(INPUT_POST, "cart_id", FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, "quantity", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    header("Location: cart.php");
    exit;
}

if (!$cartId) {
    header("Location: cart.php");
    exit;
}

if (!$quantity || $quantity < 1 || $quantity > 99) {
    $_SESSION["flash"] = "Quantity must be between 1 and 99.";
    header("Location: cart.php");
    exit;
}

try {

    $stmt = $conn->prepare(
        "UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id"
    );

    $stmt->execute([
        ":quantity" => $quantity,
        ":id" => $cartId,
        ":user_id" => (int) $_SESSION["user_id"]
    ]);

    $_SESSION["flash"] = "Cart updated.";

    header("Location: cart.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["flash"] = "Unable to update cart.";

    header("Location: cart.php");
    exit;
}