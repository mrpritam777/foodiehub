<?php

$pageTitle = "Dashboard";
$activePage = "dashboard";

include("../includes/admin_header.php");

$totalFoods = $conn->query("SELECT COUNT(*) FROM foods")->fetchColumn();

$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$totalUsers = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// Stock overview
$outOfStockCount = (int) $conn->query("SELECT COUNT(*) FROM foods WHERE stock = 0")->fetchColumn();

$lowStockCount = (int) $conn->query("SELECT COUNT(*) FROM foods WHERE stock > 0 AND stock <= 5")->fetchColumn();

$lowStockStatement = $conn->query(
    "SELECT food_name, stock
     FROM foods
     WHERE stock <= 5
     ORDER BY stock ASC, food_name ASC
     LIMIT 5"
);

$lowStockProducts = $lowStockStatement->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="d-flex">

    <?php include("../includes/admin_sidebar.php"); ?>

    <div class="flex-grow-1" style="margin-left:260px;">

        <?php include("../includes/admin_navbar.php"); ?>

        <div class="container-fluid mt-4">

            <div class="row">

                <div class="col-md-3">

                    <div class="card bg-primary text-white shadow">

                        <div class="card-body">

                            <h2><?= $totalFoods ?></h2>

                            <p>Total Products</p>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-success text-white shadow">

                        <div class="card-body">

                            <h2><?= $totalCategories ?></h2>

                            <p>Total Categories</p>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-warning text-dark shadow">

                        <div class="card-body">

                            <h2><?= $totalUsers ?></h2>

                            <p>Total Users</p>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-danger text-white shadow">

                        <div class="card-body">

                            <h2><?= $totalOrders ?></h2>

                            <p>Total Orders</p>

                        </div>

                    </div>

                </div>

            </div>

            <div class="row mt-4">

                <div class="col-md-3">

                    <div class="card bg-dark text-white shadow">

                        <div class="card-body">

                            <h2><?= $outOfStockCount ?></h2>

                            <p><i class="fa-solid fa-box-open me-1"></i>Out of Stock Products</p>

                            <a href="products/index.php" class="stretched-link"></a>

                        </div>

                    </div>

                </div>

                <div class="col-md-3">

                    <div class="card bg-info text-white shadow">

                        <div class="card-body">

                            <h2><?= $lowStockCount ?></h2>

                            <p><i class="fa-solid fa-boxes-stacked me-1"></i>Low Stock Products (1-5)</p>

                            <a href="products/index.php" class="stretched-link"></a>

                        </div>

                    </div>

                </div>

                <div class="col-md-6">

                    <div class="card shadow">

                        <div class="card-header bg-white">

                            <h6 class="mb-0">
                                <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i>
                                Low / Out of Stock Products
                            </h6>

                        </div>

                        <div class="card-body py-2">

                            <?php if (count($lowStockProducts) > 0): ?>

                                <?php foreach ($lowStockProducts as $product): ?>

                                    <div class="d-flex justify-content-between border-bottom py-2">

                                        <span><?= htmlspecialchars($product["food_name"]) ?></span>

                                        <span class="badge <?= (int) $product["stock"] === 0 ? "text-bg-danger" : "text-bg-warning" ?>">
                                            Stock: <?= (int) $product["stock"] ?>
                                        </span>

                                    </div>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <p class="text-muted mb-0 py-2">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i>
                                    All products have healthy stock.
                                </p>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>

            <hr>

            <h4 class="mt-4">

                Recent Orders

            </h4>

            <table class="table table-bordered bg-white shadow">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>User ID</th>

                        <th>Total</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>

                <tbody>

                    <?php

                    $stmt = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                    ?>

                        <tr>

                            <td><?= $row['id'] ?></td>

                            <td><?= $row['user_id'] ?></td>

                            <td>₹<?= number_format($row['total_amount'], 2) ?></td>

                            <td><?= htmlspecialchars($row['status']) ?></td>

                            <td><?= $row['order_date'] ?></td>

                        </tr>

                    <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include("../includes/admin_footer.php"); ?>