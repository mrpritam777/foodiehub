<?php

$pageTitle = "Product Management";
$activePage = "products";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$search = trim($_GET["search"] ?? "");
$categoryId = filter_input(INPUT_GET, "category_id", FILTER_VALIDATE_INT);
$status = trim($_GET["status"] ?? "");

$allowedStatuses = ["Available", "Unavailable"];

$sql = "
    SELECT
        foods.id,
        foods.food_name,
        foods.price,
        foods.description,
        foods.image,
        foods.status,
        foods.stock,
        foods.created_at,
        categories.category_name
    FROM foods
    INNER JOIN categories
        ON categories.id = foods.category_id
    WHERE 1 = 1
";

$params = [];

if ($search !== "") {
    $sql .= " AND foods.food_name LIKE :search";
    $params[":search"] = "%" . $search . "%";
}

if ($categoryId) {
    $sql .= " AND foods.category_id = :category_id";
    $params[":category_id"] = $categoryId;
}

if (in_array($status, $allowedStatuses, true)) {
    $sql .= " AND foods.status = :status";
    $params[":status"] = $status;
}

$sql .= " ORDER BY foods.id DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoryStatement = $conn->query(
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

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
                    <h3 class="mb-1">Product Management</h3>
                    <p class="text-muted mb-0">
                        Add, update stock and manage products.
                    </p>
                </div>

                <a href="create.php" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-1"></i>
                    Add Product
                </a>

            </div>

            <?php if ($successMessage !== ""): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="fa-solid fa-circle-check me-1"></i>

                    <?= htmlspecialchars($successMessage) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>

            <?php if ($errorMessage !== ""): ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fa-solid fa-circle-exclamation me-1"></i>

                    <?= htmlspecialchars($errorMessage) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                    ></button>

                </div>

            <?php endif; ?>

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <form method="GET" class="row g-3">

                        <div class="col-lg-4 col-md-6">

                            <label class="form-label">Search Product</label>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search by product name"
                                value="<?= htmlspecialchars($search) ?>"
                            >

                        </div>

                        <div class="col-lg-3 col-md-6">

                            <label class="form-label">Category</label>

                            <select name="category_id" class="form-select">

                                <option value="">All Categories</option>

                                <?php foreach ($categories as $category): ?>

                                    <option
                                        value="<?= (int) $category["id"] ?>"
                                        <?= $categoryId === (int) $category["id"] ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($category["category_name"]) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="col-lg-3 col-md-6">

                            <label class="form-label">Status</label>

                            <select name="status" class="form-select">

                                <option value="">All Status</option>

                                <option
                                    value="Available"
                                    <?= $status === "Available" ? "selected" : "" ?>
                                >
                                    Available
                                </option>

                                <option
                                    value="Unavailable"
                                    <?= $status === "Unavailable" ? "selected" : "" ?>
                                >
                                    Unavailable
                                </option>

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
                                    <th>SL</th>
                                    <th>Image</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th width="170">Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php if (count($foods) > 0): ?>

                                    <?php foreach ($foods as $index => $food): ?>

                                        <tr>

                                            <td><?= $index + 1 ?></td>

                                            <td>

                                                <?php if (!empty($food["image"])): ?>

                                                    <img
                                                        src="../../uploads/products/<?= htmlspecialchars($food["image"]) ?>"
                                                        alt="<?= htmlspecialchars($food["food_name"]) ?>"
                                                        width="70"
                                                        height="55"
                                                        class="rounded object-fit-cover"
                                                    >

                                                <?php else: ?>

                                                    <div
                                                        class="bg-light border rounded d-flex align-items-center justify-content-center"
                                                        style="width:70px;height:55px;"
                                                    >
                                                        <i class="fa-solid fa-image text-muted"></i>
                                                    </div>

                                                <?php endif; ?>

                                            </td>

                                            <td>
                                                <strong>
                                                    <?= htmlspecialchars($food["food_name"]) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars($food["category_name"]) ?>
                                            </td>

                                            <td>
                                                ₹<?= number_format((float) $food["price"], 2) ?>
                                            </td>

                                            <td>

                                                <?php if ((int) $food["stock"] < 1): ?>

                                                    <span class="badge text-bg-danger">0 — Out of Stock</span>

                                                <?php elseif ((int) $food["stock"] <= 5): ?>

                                                    <span class="badge text-bg-warning"><?= (int) $food["stock"] ?> — Low</span>

                                                <?php else: ?>

                                                    <span class="badge text-bg-success"><?= (int) $food["stock"] ?></span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <?php if ($food["status"] === "Available"): ?>

                                                    <span class="badge text-bg-success">
                                                        Available
                                                    </span>

                                                <?php else: ?>

                                                    <span class="badge text-bg-danger">
                                                        Unavailable
                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                            <td>

                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm"
                                                    title="Increase Stock"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#restockModal<?= (int) $food["id"] ?>"
                                                >
                                                    <i class="fa-solid fa-plus"></i>
                                                </button>

                                                <a
                                                    href="edit.php?id=<?= (int) $food["id"] ?>"
                                                    class="btn btn-warning btn-sm"
                                                    title="Edit Product"
                                                >
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>

                                                <form
                                                    action="delete.php"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Delete this product?')"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= htmlspecialchars(generateCsrfToken()) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $food["id"] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm"
                                                        title="Delete Product"
                                                    >
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>

                                                </form>

                                                <!-- Increase stock modal -->
                                                <div class="modal fade" id="restockModal<?= (int) $food["id"] ?>" tabindex="-1" aria-hidden="true">

                                                    <div class="modal-dialog modal-sm modal-dialog-centered">

                                                        <div class="modal-content">

                                                            <form action="add_stock.php" method="POST">

                                                                <input
                                                                    type="hidden"
                                                                    name="csrf_token"
                                                                    value="<?= htmlspecialchars(generateCsrfToken()) ?>"
                                                                >

                                                                <input
                                                                    type="hidden"
                                                                    name="id"
                                                                    value="<?= (int) $food["id"] ?>"
                                                                >

                                                                <div class="modal-header py-2">

                                                                    <h6 class="modal-title">
                                                                        <i class="fa-solid fa-boxes-stacked me-1"></i>
                                                                        Increase Stock
                                                                    </h6>

                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>

                                                                </div>

                                                                <div class="modal-body">

                                                                    <p class="mb-2">
                                                                        <strong><?= htmlspecialchars($food["food_name"]) ?></strong><br>
                                                                        <small class="text-muted">
                                                                            Current stock: <?= (int) $food["stock"] ?>
                                                                        </small>
                                                                    </p>

                                                                    <label class="form-label">
                                                                        Add quantity
                                                                        <span class="text-danger">*</span>
                                                                    </label>

                                                                    <input
                                                                        type="number"
                                                                        name="add_quantity"
                                                                        class="form-control"
                                                                        min="1"
                                                                        max="999999"
                                                                        value="10"
                                                                        required
                                                                    >

                                                                </div>

                                                                <div class="modal-footer py-2">

                                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                                        Cancel
                                                                    </button>

                                                                    <button type="submit" class="btn btn-success">
                                                                        <i class="fa-solid fa-plus me-1"></i>
                                                                        Add Stock
                                                                    </button>

                                                                </div>

                                                            </form>

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>

                                        <td colspan="8" class="text-center py-5">

                                            <i class="fa-solid fa-box-open fa-2x text-muted mb-3"></i>

                                            <p class="text-muted mb-0">
                                                No products found.
                                            </p>

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