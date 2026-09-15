<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: orders.php");
    exit;
}

$orderStatement = $conn->prepare(
    "SELECT
         id,
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
         status,
         order_date
     FROM orders
     WHERE id = :id AND user_id = :user_id
     LIMIT 1"
);

$orderStatement->execute([
    ":id" => $id,
    ":user_id" => (int) $_SESSION["user_id"]
]);

$order = $orderStatement->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION["flash"] = "Order not found.";
    header("Location: orders.php");
    exit;
}

$itemStatement = $conn->prepare(
    "SELECT
         order_items.quantity,
         order_items.price,
         foods.food_name,
         foods.image
     FROM order_items
     INNER JOIN foods ON foods.id = order_items.food_id
     WHERE order_items.order_id = :id
     ORDER BY order_items.id ASC"
);

$itemStatement->execute([":id" => $id]);

$items = $itemStatement->fetchAll(PDO::FETCH_ASSOC);

$bg = match ($order["status"]) {
    "Pending"   => "text-bg-warning",
    "Preparing" => "text-bg-info",
    "Delivered" => "text-bg-success",
    "Cancelled" => "text-bg-danger",
    default     => "text-bg-secondary",
};
?>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="mb-0">Order #<?= (int) $order["id"] ?></h2>
            <p class="text-muted mb-0">Placed on <?= date("d M Y, h:i A", strtotime($order["order_date"])) ?></p>
        </div>

        <a href="orders.php" class="btn btn-outline-danger">
            <i class="fa-solid fa-arrow-left me-1"></i>Back
        </a>

    </div>

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">

            <h5 class="mb-0">Order Status</h5>

            <span class="badge <?= $bg ?> fs-6"><?= htmlspecialchars($order["status"]) ?></span>

        </div>

        <div class="card-body">

            <div class="d-flex justify-content-between">

                <?php
                    $steps = ["Pending", "Preparing", "Delivered"];
                    $currentIndex = array_search($order["status"], $steps, true);

                    if ($order["status"] === "Cancelled") {
                        $currentIndex = -1;
                    }
                ?>

                <?php foreach ($steps as $index => $step): ?>

                    <div class="text-center flex-grow-1">

                        <div
                            class="mx-auto d-flex align-items-center justify-content-center <?= $currentIndex >= $index ? "bg-success text-white" : "bg-light border text-muted" ?>"
                            style="width:44px;height:44px;border-radius:50%;"
                        >
                            <?php if ($currentIndex > $index): ?>
                                <i class="fa-solid fa-check"></i>
                            <?php else: ?>
                                <?= $index + 1 ?>
                            <?php endif; ?>
                        </div>

                        <small class="mt-2 d-block"><?= $step ?></small>

                    </div>

                <?php endforeach; ?>

            </div>

            <?php if ($order["status"] === "Cancelled"): ?>

                <div class="alert alert-danger mt-4 mb-0">
                    <i class="fa-solid fa-circle-xmark me-1"></i>
                    This order was cancelled.
                </div>

            <?php endif; ?>

        </div>

    </div>

    <div class="row g-4">

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-truck me-1 text-danger"></i>
                        Delivery Information
                    </h5>
                </div>

                <div class="card-body">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Full Name</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["customer_name"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Phone Number</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["customer_phone"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Email</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["customer_email"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Delivery Address</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["delivery_address"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>City</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["city"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Area / Location</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["area"] ?? "—") ?></p>
                        </div>

                        <div class="col-md-6">
                            <p class="text-muted mb-1"><strong>Postal Code</strong></p>
                            <p class="mb-0"><?= htmlspecialchars($order["postal_code"] ?? "—") ?></p>
                        </div>

                        <?php if (!empty($order["order_note"])): ?>

                            <div class="col-12">
                                <p class="text-muted mb-1"><strong>Order Note</strong></p>
                                <p class="mb-0"><?= htmlspecialchars($order["order_note"]) ?></p>
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Items</h5>
                </div>

                <div class="card-body p-0">

                    <table class="table align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($items as $item): ?>

                                <tr>

                                    <td>
                                        <?php if (!empty($item["image"])): ?>
                                            <img
                                                src="../uploads/products/<?= htmlspecialchars($item["image"]) ?>"
                                                alt="<?= htmlspecialchars($item["food_name"]) ?>"
                                                width="60"
                                                height="50"
                                                class="rounded object-fit-cover"
                                            >
                                        <?php else: ?>
                                            <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:60px;height:50px;">
                                                <i class="fa-solid fa-burger text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($item["food_name"]) ?></td>

                                    <td><?= (int) $item["quantity"] ?></td>

                                    <td class="text-end">₹<?= number_format((float) $item["price"], 2) ?></td>

                                    <td class="text-end">₹<?= number_format((float) $item["price"] * (int) $item["quantity"], 2) ?></td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        <div class="col-lg-4">

            <div class="card shadow-sm">

                <div class="card-header bg-white">
                    <h5 class="mb-0">Summary</h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Items</span>
                        <span><?= count($items) ?></span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Status</span>
                        <span class="badge <?= $bg ?>"><?= htmlspecialchars($order["status"]) ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span>₹<?= number_format((float) $order["subtotal"], 2) ?></span>
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Total</span>
                        <span>₹<?= number_format((float) $order["total_amount"], 2) ?></span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>