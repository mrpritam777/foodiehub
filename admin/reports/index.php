<?php

$pageTitle = "Sales Report";
$activePage = "reports";

require_once __DIR__ . "/../../includes/admin_header.php";

$from = trim($_GET["from"] ?? "");
$to = trim($_GET["to"] ?? "");

$params = [];

$where = "1 = 1";

if ($from !== "" && strtotime($from) !== false) {
    $where .= " AND DATE(order_date) >= :from";
    $params[":from"] = date("Y-m-d", strtotime($from));
}

if ($to !== "" && strtotime($to) !== false) {
    $where .= " AND DATE(order_date) <= :to";
    $params[":to"] = date("Y-m-d", strtotime($to));
}

// Overall summary
$summaryStatement = $conn->prepare(
    "SELECT
         COUNT(*)                     AS total_orders,
         COALESCE(SUM(total_amount), 0) AS total_sales
     FROM orders
     WHERE $where"
);
$summaryStatement->execute($params);
$summary = $summaryStatement->fetch(PDO::FETCH_ASSOC);

$totalOrders = (int) $summary["total_orders"];
$totalSales = (float) $summary["total_sales"];

// Sales by status
$statusStatement = $conn->prepare(
    "SELECT status, COUNT(*) AS count, COALESCE(SUM(total_amount),0) AS amount
     FROM orders
     WHERE $where
     GROUP BY status"
);
$statusStatement->execute($params);
$statusBreakdown = $statusStatement->fetchAll(PDO::FETCH_ASSOC);

// Top-selling foods
$topFoodsStatement = $conn->prepare(
    "SELECT
         foods.food_name,
         SUM(order_items.quantity)                  AS total_qty,
         SUM(order_items.price * order_items.quantity) AS revenue
     FROM order_items
     INNER JOIN orders ON orders.id = order_items.order_id
     INNER JOIN foods ON foods.id = order_items.food_id
     WHERE $where
     GROUP BY foods.id, foods.food_name
     ORDER BY total_qty DESC
     LIMIT 5"
);
$topFoodsStatement->execute($params);
$topFoods = $topFoodsStatement->fetchAll(PDO::FETCH_ASSOC);

// Daily sales for the filtered range
$dailyStatement = $conn->prepare(
    "SELECT DATE(order_date) AS day, COUNT(*) AS count, COALESCE(SUM(total_amount),0) AS amount
     FROM orders
     WHERE $where
     GROUP BY DATE(order_date)
     ORDER BY day ASC"
);
$dailyStatement->execute($params);
$dailySales = $dailyStatement->fetchAll(PDO::FETCH_ASSOC);

$successMessage = $_SESSION["success"] ?? "";
unset($_SESSION["success"]);
?>

<div class="d-flex">

    <?php require_once __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content flex-grow-1">

        <?php require_once __DIR__ . "/../../includes/admin_navbar.php"; ?>

        <div class="container-fluid py-4">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

                <div>
                    <h3 class="mb-1">Sales Report</h3>
                    <p class="text-muted mb-0">
                        Track sales, orders and top-selling products.
                    </p>
                </div>

            </div>

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <form method="GET" class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">From Date</label>

                            <input type="date" name="from" class="form-control" value="<?= htmlspecialchars($from) ?>">

                        </div>

                        <div class="col-md-4">

                            <label class="form-label">To Date</label>

                            <input type="date" name="to" class="form-control" value="<?= htmlspecialchars($to) ?>">

                        </div>

                        <div class="col-md-2 d-flex align-items-end gap-2">

                            <button type="submit" class="btn btn-dark flex-grow-1">Filter</button>

                            <a href="index.php" class="btn btn-outline-secondary" title="Reset">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>

                        </div>

                    </form>

                </div>

            </div>

            <div class="row g-4 mb-4">

                <div class="col-md-3">

                    <div class="card bg-primary text-white shadow">
                        <div class="card-body">
                            <h2><?= $totalOrders ?></h2>
                            <p>Total Orders</p>
                        </div>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-success text-white shadow">
                        <div class="card-body">
                            <h2>₹<?= number_format($totalSales, 2) ?></h2>
                            <p>Total Sales</p>
                        </div>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-warning text-dark shadow">
                        <div class="card-body">
                            <h2><?= $totalOrders > 0 ? number_format($totalSales / $totalOrders, 2) : "0.00" ?></h2>
                            <p>Average Order Value</p>
                        </div>
                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-info text-white shadow">
                        <div class="card-body">
                            <h2><?= count($dailySales) ?></h2>
                            <p>Active Days</p>
                        </div>
                    </div>

                </div>

            </div>

            <div class="row g-4">

                <div class="col-lg-6">

                    <div class="card shadow-sm h-100">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Orders by Status</h5>
                        </div>

                        <div class="card-body p-0">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th>Status</th>
                                        <th class="text-center">Orders</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if (count($statusBreakdown) > 0): ?>

                                        <?php foreach ($statusBreakdown as $row): ?>

                                            <tr>

                                                <td>
                                                    <?php
                                                        $bg = match ($row["status"]) {
                                                            "Pending"   => "text-bg-warning",
                                                            "Preparing" => "text-bg-info",
                                                            "Delivered" => "text-bg-success",
                                                            "Cancelled" => "text-bg-danger",
                                                            default     => "text-bg-secondary",
                                                        };
                                                    ?>
                                                    <span class="badge <?= $bg ?>"><?= htmlspecialchars($row["status"]) ?></span>
                                                </td>

                                                <td class="text-center"><?= (int) $row["count"] ?></td>

                                                <td class="text-end">₹<?= number_format((float) $row["amount"], 2) ?></td>

                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No data in range.</td>
                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="card shadow-sm h-100">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Top Selling Products</h5>
                        </div>

                        <div class="card-body p-0">

                            <table class="table table-hover align-middle mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Qty Sold</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php if (count($topFoods) > 0): ?>

                                        <?php foreach ($topFoods as $row): ?>

                                            <tr>
                                                <td><?= htmlspecialchars($row["food_name"]) ?></td>
                                                <td class="text-center"><?= (int) $row["total_qty"] ?></td>
                                                <td class="text-end">₹<?= number_format((float) $row["revenue"], 2) ?></td>
                                            </tr>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">No sales in range.</td>
                                        </tr>

                                    <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

                <div class="col-12">

                    <div class="card shadow-sm">

                        <div class="card-header bg-white">
                            <h5 class="mb-0">Daily Sales</h5>
                        </div>

                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-end">Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <?php if (count($dailySales) > 0): ?>

                                            <?php foreach ($dailySales as $row): ?>

                                                <tr>
                                                    <td><?= date("d M Y", strtotime($row["day"])) ?></td>
                                                    <td class="text-center"><?= (int) $row["count"] ?></td>
                                                    <td class="text-end">₹<?= number_format((float) $row["amount"], 2) ?></td>
                                                </tr>

                                            <?php endforeach; ?>

                                        <?php else: ?>

                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No sales in range.</td>
                                            </tr>

                                        <?php endif; ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . "/../../includes/admin_footer.php"; ?>