<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$foodId = filter_input(INPUT_POST, "food_id", FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, "quantity", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    header("Location: index.php");
    exit;
}

if (!$foodId || !$quantity || $quantity < 1 || $quantity > 99) {
    header("Location: index.php");
    exit;
}

try {

    // Food must exist, be available and have enough stock
    $foodStatement = $conn->prepare(
        "SELECT id, food_name, stock
         FROM foods
         WHERE id = :id AND status = 'Available'
         LIMIT 1"
    );

    $foodStatement->execute([":id" => $foodId]);

    $food = $foodStatement->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        $_SESSION["error"] = "Product not available.";
        header("Location: index.php");
        exit;
    }

    // Low stock check: no order when the product is out of stock
    if ((int) $food["stock"] < 1) {
        $_SESSION["error"] = "Sorry, \"" . $food["food_name"] . "\" is out of stock.";
        header("Location: index.php");
        exit;
    }

    // Insert or increment quantity
    $existingStatement = $conn->prepare(
        "SELECT id, quantity FROM cart WHERE user_id = :user_id AND food_id = :food_id LIMIT 1"
    );

    $existingStatement->execute([
        ":user_id" => (int) $_SESSION["user_id"],
        ":food_id" => $foodId
    ]);

    $existing = $existingStatement->fetch(PDO::FETCH_ASSOC);

    $maxQuantity = min((int) $food["stock"], 99);

    if ($existing) {
        $newQuantity = (int) $existing["quantity"] + $quantity;

        if ($newQuantity > $maxQuantity) {
            $_SESSION["error"] = "Only " . (int) $food["stock"] . " unit(s) of \"" . $food["food_name"] . "\" are in stock.";
            header("Location: cart.php");
            exit;
        }

        $updateStatement = $conn->prepare(
            "UPDATE cart SET quantity = :quantity WHERE id = :id"
        );

        $updateStatement->execute([
            ":quantity" => $newQuantity,
            ":id" => (int) $existing["id"]
        ]);
    } else {

        if ($quantity > $maxQuantity) {
            $_SESSION["error"] = "Only " . (int) $food["stock"] . " unit(s) of \"" . $food["food_name"] . "\" are in stock.";
            header("Location: index.php");
            exit;
        }

        $insertStatement = $conn->prepare(
            "INSERT INTO cart (user_id, food_id, quantity) VALUES (:user_id, :food_id, :quantity)"
        );

        $insertStatement->execute([
            ":user_id" => (int) $_SESSION["user_id"],
            ":food_id" => $foodId,
            ":quantity" => $quantity
        ]);
    }

    $_SESSION["flash"] = "Item added to cart.";

    header("Location: cart.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["flash"] = "Unable to add item to cart.";

    header("Location: index.php");
    exit;
}