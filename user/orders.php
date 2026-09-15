<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$stmt = $conn->prepare(
    "SELECT
         orders.id,
         orders.total_amount,
         orders.status,
         orders.order_date,
         (SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) AS item_count
     FROM orders
     WHERE orders.user_id = :user_id
     ORDER BY orders.id DESC"
);

$stmt->execute([":user_id" => (int) $_SESSION["user_id"]]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);
?>

<div class="container py-4">

    <?php if ($flash !== ""): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-1"></i>
            <?= htmlspecialchars($flash) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="mb-0">My Orders</h2>

        <a href="index.php" class="btn btn-outline-danger">
            <i class="fa-solid fa-box me-1"></i>Order More
        </a>

    </div>

    <?php if (count($orders) > 0): ?>

        <div class="card shadow-sm">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Order ID</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($orders as $order): ?>

                                <tr>

                                    <td><strong>#<?= (int) $order["id"] ?></strong></td>

                                    <td><?= (int) $order["item_count"] ?></td>

                                    <td>₹<?= number_format((float) $order["total_amount"], 2) ?></td>

                                    <td>

                                        <?php
                                            $bg = match ($order["status"]) {
                                                "Pending"   => "text-bg-warning",
                                                "Preparing" => "text-bg-info",
                                                "Delivered" => "text-bg-success",
                                                "Cancelled" => "text-bg-danger",
                                                default     => "text-bg-secondary",
                                            };
                                        ?>

                                        <span class="badge <?= $bg ?>"><?= htmlspecialchars($order["status"]) ?></span>

                                    </td>

                                    <td><?= date("d M Y, h:i A", strtotime($order["order_date"])) ?></td>

                                    <td>

                                        <a href="order_details.php?id=<?= (int) $order["id"] ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-eye me-1"></i>View
                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fa-solid fa-receipt fa-3x text-muted mb-3"></i>

                <h5>No orders yet</h5>

                <p class="text-muted">When you place an order it will appear here.</p>

                <a href="index.php" class="btn btn-danger">Browse Products</a>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>