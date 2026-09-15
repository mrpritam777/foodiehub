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

    // The cart row must belong to the user; join the product for the stock check
    $cartStatement = $conn->prepare(
        "SELECT
             cart.id          AS cart_id,
             foods.id         AS food_id,
             foods.food_name,
             foods.stock
         FROM cart
         INNER JOIN foods ON foods.id = cart.food_id
         WHERE cart.id = :id
           AND cart.user_id = :user_id
         LIMIT 1"
    );

    $cartStatement->execute([
        ":id" => $cartId,
        ":user_id" => (int) $_SESSION["user_id"]
    ]);

    $cartItem = $cartStatement->fetch(PDO::FETCH_ASSOC);

    if (!$cartItem) {
        $_SESSION["flash"] = "Cart item not found.";
        header("Location: cart.php");
        exit;
    }

    // Stock check: quantity cannot exceed the available stock
    if ($quantity > (int) $cartItem["stock"]) {
        $_SESSION["error"] = "Only " . (int) $cartItem["stock"] . " unit(s) of \"" . $cartItem["food_name"] . "\" are in stock.";
        header("Location: cart.php");
        exit;
    }

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