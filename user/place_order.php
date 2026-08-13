<?php

require_once __DIR__ . "/../includes/user_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    header("Location: cart.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

// --- Delivery information ----------------------------------------------
$customerName = trim($_POST["customer_name"] ?? "");
$customerPhone = trim($_POST["customer_phone"] ?? "");
$customerEmail = trim($_POST["customer_email"] ?? "");
$deliveryAddress = trim($_POST["delivery_address"] ?? "");
$city = trim($_POST["city"] ?? "");
$area = trim($_POST["area"] ?? "");
$postalCode = trim($_POST["postal_code"] ?? "");
$orderNote = trim($_POST["order_note"] ?? "");

$_SESSION["checkout_old"] = [
    "customer_name" => $customerName,
    "customer_phone" => $customerPhone,
    "customer_email" => $customerEmail,
    "delivery_address" => $deliveryAddress,
    "city" => $city,
    "area" => $area,
    "postal_code" => $postalCode,
    "order_note" => $orderNote
];

// Validation
if ($customerName === "" || mb_strlen($customerName) > 100) {
    $_SESSION["error"] = "Full name is required (max 100 characters).";
    header("Location: checkout.php");
    exit;
}

if ($customerPhone === "" || mb_strlen($customerPhone) > 20) {
    $_SESSION["error"] = "Phone number is required (max 20 characters).";
    header("Location: checkout.php");
    exit;
}

if (
    $customerEmail === "" ||
    !filter_var($customerEmail, FILTER_VALIDATE_EMAIL) ||
    mb_strlen($customerEmail) > 150
) {
    $_SESSION["error"] = "A valid email address is required.";
    header("Location: checkout.php");
    exit;
}

if ($deliveryAddress === "" || mb_strlen($deliveryAddress) > 500) {
    $_SESSION["error"] = "Delivery address is required (max 500 characters).";
    header("Location: checkout.php");
    exit;
}

if ($city === "" || mb_strlen($city) > 100) {
    $_SESSION["error"] = "City is required (max 100 characters).";
    header("Location: checkout.php");
    exit;
}

if ($area === "" || mb_strlen($area) > 100) {
    $_SESSION["error"] = "Area / location is required (max 100 characters).";
    header("Location: checkout.php");
    exit;
}

if (mb_strlen($postalCode) > 20) {
    $_SESSION["error"] = "Postal code cannot exceed 20 characters.";
    header("Location: checkout.php");
    exit;
}

if (mb_strlen($orderNote) > 1000) {
    $_SESSION["error"] = "Order note cannot exceed 1000 characters.";
    header("Location: checkout.php");
    exit;
}

try {

    $conn->beginTransaction();

    // Lock the user's cart (only available items)
    $cartStatement = $conn->prepare(
        "SELECT
             cart.id      AS cart_id,
             cart.quantity,
             foods.id      AS food_id,
             foods.price
         FROM cart
         INNER JOIN foods ON foods.id = cart.food_id AND foods.status = 'Available'
         WHERE cart.user_id = :user_id
         FOR UPDATE"
    );

    $cartStatement->execute([":user_id" => $userId]);

    $cartItems = $cartStatement->fetchAll(PDO::FETCH_ASSOC);

    if (count($cartItems) === 0) {
        $conn->rollBack();
        $_SESSION["flash"] = "Your cart is empty.";
        header("Location: cart.php");
        exit;
    }

    $subtotal = 0.0;

    foreach ($cartItems as $item) {
        $subtotal += (float) $item["price"] * (int) $item["quantity"];
    }

    $subtotal = round($subtotal, 2);

    $totalAmount = $subtotal;

    // Create the order
    $orderStatement = $conn->prepare(
        "INSERT INTO orders (
             user_id,
             customer_name,
             customer_phone,
             customer_email,
             delivery_address,
             city,
             area,
             postal_code,
             order_note,
             subtotal,
             total_amount,
             status
         ) VALUES (
             :user_id,
             :customer_name,
             :customer_phone,
             :customer_email,
             :delivery_address,
             :city,
             :area,
             :postal_code,
             :order_note,
             :subtotal,
             :total_amount,
             'Pending'
         )"
    );

    $orderStatement->execute([
        ":user_id" => $userId,
        ":customer_name" => $customerName,
        ":customer_phone" => $customerPhone,
        ":customer_email" => $customerEmail,
        ":delivery_address" => $deliveryAddress,
        ":city" => $city,
        ":area" => $area,
        ":postal_code" => $postalCode !== "" ? $postalCode : null,
        ":order_note" => $orderNote !== "" ? $orderNote : null,
        ":subtotal" => $subtotal,
        ":total_amount" => $totalAmount
    ]);

    $orderId = (int) $conn->lastInsertId();

    // Insert order items
    $itemStatement = $conn->prepare(
        "INSERT INTO order_items (order_id, food_id, quantity, price)
         VALUES (:order_id, :food_id, :quantity, :price)"
    );

    foreach ($cartItems as $item) {
        $itemStatement->execute([
            ":order_id" => $orderId,
            ":food_id" => (int) $item["food_id"],
            ":quantity" => (int) $item["quantity"],
            ":price" => (float) $item["price"]
        ]);
    }

    // Clear the cart
    $clearStatement = $conn->prepare("DELETE FROM cart WHERE user_id = :user_id");
    $clearStatement->execute([":user_id" => $userId]);

    unset($_SESSION["checkout_old"]);

    $conn->commit();

    $_SESSION["order_success"] = $orderId;

    header("Location: order_confirmation.php");
    exit;

} catch (PDOException $exception) {

    $conn->rollBack();

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to place your order. Please try again.";

    header("Location: checkout.php");
    exit;
}
