<?php

$pageTitle = "Order Details";
$activePage = "orders";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION["error"] = "Invalid order ID.";
    header("Location: index.php");
    exit;
}

$orderStatement = $conn->prepare(
    "SELECT
         orders.id,
         orders.customer_name,
         orders.customer_phone,
         orders.customer_email,
         orders.delivery_address,
         orders.city,
         orders.area,
         orders.postal_code,
         orders.order_note,
         orders.subtotal,
         orders.total_amount,
         orders.status,
         orders.order_date,
         users.name,
         users.email,
         users.phone
     FROM orders
     INNER JOIN users ON users.id = orders.user_id
     WHERE orders.id = :id
     LIMIT 1"
);

$orderStatement->execute([":id" => $id]);

$order = $orderStatement->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    $_SESSION["error"] = "Order not found.";
    header("Location: index.php");
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

$badgeClass = match ($order["status"]) {
    "Pending"    => "text-bg-warning",
    "Preparing"  => "text-bg-info",
    "Delivered"  => "text-bg-success",
    "Cancelled"  => "text-bg-danger",
    default      => "text-bg-secondary",
};

$errorMessage = $_SESSION["error"] ?? "";
unset($_SESSION["error"]);
?>

<div class="d-flex">

    <?php require_once __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content flex-grow-1">

        <?php require_once __DIR__ . "/../../includes/admin_navbar.php"; ?>

        <div class="container-fluid py-4">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h3 class="mb-1">Order #<?= (int) $order["id"] ?></h3>
                    <p class="text-muted mb-0">
                        Placed on <?= date("d M Y, h:i A", strtotime($order["order_date"])) ?>
                    </p>
                </div>

                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left me-1"></i>
                    Back
                </a>

            </div>

            <?php if ($errorMessage !== ""): ?>

                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

            <?php endif; ?>

            <div class="row g-4">

                <div class="col-lg-8">

                    <div class="card shadow-sm mb-4">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Order Items</h5>
                        </div>

                        <div class="card-body p-0">

                            <table class="table align-middle mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Food</th>
                                        <th>Quantity</th>
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
                                                        src="../../uploads/foods/<?= htmlspecialchars($item["image"]) ?>"
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

                    <div class="card shadow-sm">

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
                                    <p class="mb-0"><?= htmlspecialchars($order["customer_name"] ?? $order["name"] ?? "—") ?></p>
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong>Phone Number</strong></p>
                                    <p class="mb-0"><?= htmlspecialchars($order["customer_phone"] ?? $order["phone"] ?? "—") ?></p>
                                </div>

                                <div class="col-md-6">
                                    <p class="text-muted mb-1"><strong>Email</strong></p>
                                    <p class="mb-0"><?= htmlspecialchars($order["customer_email"] ?? $order["email"] ?? "—") ?></p>
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

                </div>

                <div class="col-lg-4">

                    <div class="card shadow-sm mb-4">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Summary</h5>
                        </div>

                        <div class="card-body">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Status</span>
                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($order["status"]) ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Items</span>
                                <span><?= count($items) ?></span>
                            </div>

                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Order Date</span>
                                <span><?= date("d M Y, h:i A", strtotime($order["order_date"])) ?></span>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal</span>
                                <span>₹<?= number_format((float) $order["subtotal"], 2) ?></span>
                            </div>

                            <div class="d-flex justify-content-between fw-bold fs-5">
                                <span>Total Amount</span>
                                <span>₹<?= number_format((float) $order["total_amount"], 2) ?></span>
                            </div>

                        </div>

                    </div>

                    <div class="card shadow-sm">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Customer</h5>
                        </div>

                        <div class="card-body">

                            <p class="mb-1"><strong><?= htmlspecialchars($order["name"]) ?></strong></p>
                            <p class="mb-1 text-muted">
                                <i class="fa-solid fa-envelope me-1"></i><?= htmlspecialchars($order["email"]) ?>
                            </p>
                            <p class="mb-0 text-muted">
                                <i class="fa-solid fa-phone me-1"></i><?= htmlspecialchars($order["phone"] ?? "—") ?>
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . "/../../includes/admin_footer.php"; ?>