<?php

$pageTitle = "Dashboard";
$activePage = "dashboard";

include("../includes/admin_header.php");

$totalFoods = $conn->query("SELECT COUNT(*) FROM foods")->fetchColumn();

$totalCategories = $conn->query("SELECT COUNT(*) FROM categories")->fetchColumn();

$totalUsers = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

$totalOrders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();

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

                            <p>Total Foods</p>

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