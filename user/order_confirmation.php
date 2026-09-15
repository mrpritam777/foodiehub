<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$orderId = $_SESSION["order_success"] ?? null;

if (!$orderId) {
    header("Location: index.php");
    exit;
}

$orderStatement = $conn->prepare(
    "SELECT id, subtotal, total_amount, status, order_date
     FROM orders
     WHERE id = :id AND user_id = :user_id
     LIMIT 1"
);

$orderStatement->execute([
    ":id" => (int) $orderId,
    ":user_id" => (int) $_SESSION["user_id"]
]);

$order = $orderStatement->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    unset($_SESSION["order_success"]);
    header("Location: index.php");
    exit;
}

unset($_SESSION["order_success"]);
?>

<div class="container py-5">

    <div class="text-center mb-4">

        <i class="fa-solid fa-circle-check text-success" style="font-size:64px;"></i>

        <h2 class="mt-3">Order Placed Successfully!</h2>

        <p class="text-muted">
            Thank you for ordering from ProductHub. Your order has been received.
        </p>

    </div>

    <div class="row justify-content-center">

        <div class="col-md-6 col-lg-5">

            <div class="card shadow-sm">

                <div class="card-header bg-white text-center">
                    <h5 class="mb-0">Order #<?= (int) $order["id"] ?></h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <strong>₹<?= number_format((float) $order["subtotal"], 2) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Amount</span>
                        <strong>₹<?= number_format((float) $order["total_amount"], 2) ?></strong>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge text-bg-warning"><?= htmlspecialchars($order["status"]) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Date</span>
                        <span><?= date("d M Y, h:i A", strtotime($order["order_date"])) ?></span>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">

                        <a href="orders.php" class="btn btn-danger">
                            <i class="fa-solid fa-list me-1"></i>Track My Orders
                        </a>

                        <a href="index.php" class="btn btn-light border">
                            Continue Shopping
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>