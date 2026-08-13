<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$orderId = filter_input(INPUT_POST, "order_id", FILTER_VALIDATE_INT);
$status = trim($_POST["status"] ?? "");
$csrfToken = $_POST["csrf_token"] ?? null;

if (!$orderId) {
    $_SESSION["error"] = "Invalid order ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: index.php");
    exit;
}

$allowedStatuses = ["Pending", "Preparing", "Delivered", "Cancelled"];

if (!in_array($status, $allowedStatuses, true)) {
    $_SESSION["error"] = "Invalid order status selected.";
    header("Location: index.php");
    exit;
}

try {

    $orderStatement = $conn->prepare(
        "SELECT id FROM orders WHERE id = :id LIMIT 1"
    );

    $orderStatement->execute([":id" => $orderId]);

    if (!$orderStatement->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["error"] = "Order not found.";
        header("Location: index.php");
        exit;
    }

    $updateStatement = $conn->prepare(
        "UPDATE orders SET status = :status WHERE id = :id"
    );

    $updateStatement->execute([
        ":status" => $status,
        ":id" => $orderId
    ]);

    $_SESSION["success"] = "Order #" . $orderId . " marked as " . $status . ".";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to update order status. Please try again.";

    header("Location: index.php");
    exit;
}