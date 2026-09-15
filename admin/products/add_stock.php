<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$addQuantity = filter_input(INPUT_POST, "add_quantity", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!$id) {
    $_SESSION["error"] = "Invalid product ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: index.php");
    exit;
}

if (!$addQuantity || $addQuantity < 1 || $addQuantity > 999999) {
    $_SESSION["error"] = "Please enter a valid quantity to add (1 - 999999).";
    header("Location: index.php");
    exit;
}

try {

  
    $productStatement = $conn->prepare(
        "SELECT id, food_name, stock
         FROM foods
         WHERE id = :id
         LIMIT 1"
    );

    $productStatement->execute([":id" => $id]);

    $product = $productStatement->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION["error"] = "Product not found.";
        header("Location: index.php");
        exit;
    }

    $conn->beginTransaction();

    $updateStatement = $conn->prepare(
        "UPDATE foods
         SET stock = stock + :add_quantity
         WHERE id = :id"
    );

    $updateStatement->execute([
        ":add_quantity" => $addQuantity,
        ":id" => $id
    ]);

    $conn->commit();

    $newStock = (int) $product["stock"] + $addQuantity;

    $_SESSION["success"] = "Stock increased by " . $addQuantity . " for \"" . $product["food_name"] . "\". New stock: " . $newStock . ".";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to increase the stock. Please try again.";

    header("Location: index.php");
    exit;
}
