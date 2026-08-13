<?php

$pageTitle = "Order Management";
$activePage = "orders";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$status = trim($_GET["status"] ?? "");
$search = trim($_GET["search"] ?? "");

$allowedStatuses = ["Pending", "Preparing", "Delivered", "Cancelled"];

$sql = "
    SELECT
        orders.id,
        orders.total_amount,
        orders.status,
        orders.order_date,
        users.name   AS user_name,
        users.email  AS user_email,
        (SELECT COUNT(*) FROM order_items WHERE order_items.order_id = orders.id) AS item_count
    FROM orders
    INNER JOIN users ON users.id = orders.user_id
    WHERE 1 = 1
";

$params = [];

if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND orders.status = :status";
    $params[":status"] = $status;
}

if ($search !== "") {
    $sql .= " AND (
        orders.id LIKE :search
        OR users.name LIKE :search
        OR users.email LIKE :search
    )";
    $params[":search"] = "%" . $search . "%";
}

$sql .= " ORDER BY orders.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$successMessage = $_SESSION["success"] ?? "";
$errorMessage = $_SESSION["error"] ?? "";

unset($_SESSION["success"], $_SESSION["error"]);
?>

<div class="d-flex">

    <?php require_once __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content flex-grow-1">

        <?php require_once __DIR__ . "/../../includes/admin_navbar.php"; ?>

        <div class="container-fluid py-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h3 class="mb-1">Order Management</h3>
                    <p class="text-muted mb-0">
                        View and update customer orders.
                    </p>
                </div>

            </div>

            <?php if ($successMessage !== ""): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="fa-solid fa-circle-check me-1"></i>
                    <?= htmlspecialchars($successMessage) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    <?= htmlspecialchars($errorMessage) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php endif; ?>

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <form method="GET" class="row g-3">

                        <div class="col-lg-4 col-md-6">

                            <label class="form-label">Search Order / User</label>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Order ID, user name or email"
                                value="<?= htmlspecialchars($search) ?>"
                            >

                        </div>

                        <div class="col-lg-3 col-md-6">

                            <label class="form-label">Status</label>

                            <select name="status" class="form-select">

                                <option value="">All Status</option>

                                <?php foreach ($allowedStatuses as $allowed): ?>

                                    <option value="<?= $allowed ?>" <?= $status === $allowed ? "selected" : "" ?>>
                                        <?= $allowed ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-lg-2 col-md-6 d-flex align-items-end gap-2">

                            <button type="submit" class="btn btn-dark flex-grow-1">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </button>

                            <a href="index.php" class="btn btn-outline-secondary">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>

                        </div>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-hover align-middle">

                            <thead class="table-dark">

                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th width="260">Actions</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($orders) > 0): ?>

                                    <?php foreach ($orders as $order): ?>

                                        <tr>

                                            <td>
                                                <a href="view.php?id=<?= (int) $order["id"] ?>" class="fw-semibold text-decoration-none">
                                                    #<?= (int) $order["id"] ?>
                                                </a>
                                            </td>

                                            <td>
                                                <strong><?= htmlspecialchars($order["user_name"]) ?></strong>
                                                <br>
                                                <small class="text-muted"><?= htmlspecialchars($order["user_email"]) ?></small>
                                            </td>

                                            <td><?= (int) $order["item_count"] ?></td>

                                            <td>₹<?= number_format((float) $order["total_amount"], 2) ?></td>

                                            <td>

                                                <?php
                                                    $badgeClass = match ($order["status"]) {
                                                        "Pending"    => "text-bg-warning",
                                                        "Preparing"  => "text-bg-info",
                                                        "Delivered"  => "text-bg-success",
                                                        "Cancelled"  => "text-bg-danger",
                                                        default      => "text-bg-secondary",
                                                    };
                                                ?>

                                                <span class="badge <?= $badgeClass ?>">
                                                    <?= htmlspecialchars($order["status"]) ?>
                                                </span>

                                            </td>

                                            <td><?= date("d M Y, h:i A", strtotime($order["order_date"])) ?></td>

                                            <td>

                                                <a href="view.php?id=<?= (int) $order["id"] ?>" class="btn btn-primary btn-sm">
                                                    <i class="fa-solid fa-eye"></i> View
                                                </a>

                                                <form
                                                    action="update_status.php"
                                                    method="POST"
                                                    class="d-inline"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= htmlspecialchars(generateCsrfToken()) ?>"
                                                    >

                                                    <input type="hidden" name="order_id" value="<?= (int) $order["id"] ?>">

                                                    <select
                                                        name="status"
                                                        class="form-select form-select-sm d-inline-block w-auto"
                                                        onchange="this.form.submit()"
                                                        title="Change Status"
                                                    >
                                                        <?php foreach ($allowedStatuses as $allowed): ?>

                                                            <option value="<?= $allowed ?>" <?= $order["status"] === $allowed ? "selected" : "" ?>>
                                                                <?= $allowed ?>
                                                            </option>

                                                        <?php endforeach; ?>

                                                    </select>

                                                </form>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fa-solid fa-cart-shopping fa-2x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No orders found.</p>
                                        </td>
                                    </tr>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . "/../../includes/admin_footer.php"; ?>