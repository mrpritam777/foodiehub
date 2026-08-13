<?php

$pageTitle = "User Management";
$activePage = "users";

require_once __DIR__ . "/../../includes/admin_header.php";

$search = trim($_GET["search"] ?? "");
$role = trim($_GET["role"] ?? "");

$sql = "
    SELECT id, name, email, phone, role, created_at,
           (SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) AS total_orders
    FROM users
    WHERE 1 = 1
";

$params = [];

if ($search !== "") {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR phone LIKE :search)";
    $params[":search"] = "%" . $search . "%";
}

if (in_array($role, ["user", "admin"], true)) {
    $sql .= " AND role = :role";
    $params[":role"] = $role;
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
                    <h3 class="mb-1">User Management</h3>
                    <p class="text-muted mb-0">
                        View registered customers and admins.
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

                            <label class="form-label">Search User</label>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Name, email or phone"
                                value="<?= htmlspecialchars($search) ?>"
                            >

                        </div>

                        <div class="col-lg-3 col-md-6">

                            <label class="form-label">Role</label>

                            <select name="role" class="form-select">

                                <option value="">All Roles</option>

                                <option value="user" <?= $role === "user" ? "selected" : "" ?>>User</option>

                                <option value="admin" <?= $role === "admin" ? "selected" : "" ?>>Admin</option>

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
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Total Orders</th>
                                    <th>Joined</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($users) > 0): ?>

                                    <?php foreach ($users as $user): ?>

                                        <tr>

                                            <td><?= (int) $user["id"] ?></td>

                                            <td>
                                                <strong><?= htmlspecialchars($user["name"]) ?></strong>
                                            </td>

                                            <td><?= htmlspecialchars($user["email"]) ?></td>

                                            <td><?= htmlspecialchars($user["phone"] ?? "—") ?></td>

                                            <td>

                                                <?php if ($user["role"] === "admin"): ?>

                                                    <span class="badge text-bg-dark">Admin</span>

                                                <?php else: ?>

                                                    <span class="badge text-bg-primary">User</span>

                                                <?php endif; ?>

                                            </td>

                                            <td><?= (int) $user["total_orders"] ?></td>

                                            <td><?= date("d M Y", strtotime($user["created_at"])) ?></td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fa-solid fa-users fa-2x text-muted mb-3"></i>
                                            <p class="text-muted mb-0">No users found.</p>
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