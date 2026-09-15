<?php

require_once __DIR__ . "/../includes/user_header.php";

$search = trim($_GET["search"] ?? "");
$categoryId = filter_input(INPUT_GET, "category_id", FILTER_VALIDATE_INT);
$sort = trim($_GET["sort"] ?? "");

$allowedSorts = [
    "name_asc" => "food_name ASC",
    "name_desc" => "food_name DESC",
    "price_asc" => "foods.price ASC",
    "price_desc" => "foods.price DESC",
    "newest" => "foods.id DESC",
];

$orderBy = $allowedSorts[$sort] ?? "foods.food_name ASC";

$sql = "
    SELECT
        foods.id,
        foods.food_name,
        foods.price,
        foods.description,
        foods.image,
        foods.status,
        foods.stock,
        categories.category_name
    FROM foods
    INNER JOIN categories ON categories.id = foods.category_id
    WHERE foods.status = 'Available'
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

$sql .= " ORDER BY " . $orderBy;

$stmt = $conn->prepare($sql);
$stmt->execute($params);

$foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categoryStatement = $conn->query(
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);
?>

<?php if ($flash !== ""): ?>

    <div class="container mt-4">

        <div class="alert alert-success alert-dismissible fade show mb-0">

            <i class="fa-solid fa-circle-check me-1"></i>
            <?= htmlspecialchars($flash) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    </div>

<?php endif; ?>

<div class="container py-4">

    <div class="row mb-4">

        <div class="col-lg-8 col-md-7">

            <h2 class="mb-1">Our Products</h2>

            <p class="text-muted mb-0">Browse and order your favorite products online.</p>

        </div>

        <div class="col-lg-4 col-md-5">

            <form method="GET" class="row g-2">

                <div class="col-7">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search products..."
                        value="<?= htmlspecialchars($search) ?>"
                    >

                </div>

                <div class="col-5">

                    <select name="sort" class="form-select" onchange="this.form.submit()">

                        <option value="">Sort by Price</option>

                        <option value="price_asc" <?= $sort === "price_asc" ? "selected" : "" ?>>Price: Low to High</option>

                        <option value="price_desc" <?= $sort === "price_desc" ? "selected" : "" ?>>Price: High to Low</option>

                        <option value="name_asc" <?= $sort === "name_asc" ? "selected" : "" ?>>Name: A to Z</option>

                        <option value="newest" <?= $sort === "newest" ? "selected" : "" ?>>Newest First</option>

                    </select>

                </div>

            </form>

        </div>

    </div>

    <div class="row mb-4">

        <div class="col">

            <div class="d-flex flex-wrap gap-2">

                <a
                    href="index.php?<?= $search !== "" ? "search=" . urlencode($search) . "&" : "" ?>sort=<?= urlencode($sort) ?>"
                    class="btn btn-sm <?= !$categoryId ? "btn-danger" : "btn-outline-secondary" ?>"
                >
                    All
                </a>

                <?php foreach ($categories as $category): ?>

                    <?php
                        $query = [];

                        if ($search !== "") $query["search"] = $search;
                        if ($sort !== "") $query["sort"] = $sort;

                        $query["category_id"] = $category["id"];

                        $href = "index.php?" . http_build_query($query);
                    ?>

                    <a
                        href="<?= $href ?>"
                        class="btn btn-sm <?= $categoryId === (int) $category["id"] ? "btn-danger" : "btn-outline-secondary" ?>"
                    >
                        <?= htmlspecialchars($category["category_name"]) ?>
                    </a>

                <?php endforeach; ?>

            </div>

        </div>

    </div>

    <?php if (count($foods) > 0): ?>

        <div class="row g-4">

            <?php foreach ($foods as $food): ?>

                <div class="col-sm-6 col-lg-4 col-xl-3">

                    <div class="card product-card h-100 shadow-sm">

                        <?php if (!empty($food["image"])): ?>

                            <img
                                src="../uploads/products/<?= htmlspecialchars($food["image"]) ?>"
                                alt="<?= htmlspecialchars($food["food_name"]) ?>"
                                class="card-img-top product-img"
                            >

                        <?php else: ?>

                            <div class="product-img bg-light d-flex align-items-center justify-content-center">
                                <i class="fa-solid fa-burger fa-3x text-muted"></i>
                            </div>

                        <?php endif; ?>

                        <div class="card-body d-flex flex-column">

                            <div class="d-flex justify-content-between align-items-start mb-1">

                                <h5 class="card-title mb-0"><?= htmlspecialchars($food["food_name"]) ?></h5>

                                <span class="badge bg-danger">₹<?= number_format((float) $food["price"], 2) ?></span>

                            </div>

                            <small class="text-muted"><?= htmlspecialchars($food["category_name"]) ?></small>

                            <p class="card-text small text-muted mt-2 flex-grow-1">
                                <?= htmlspecialchars(mb_strimwidth($food["description"] ?? "No description available.", 0, 90, "...")) ?>
                            </p>

                            <?php if ((int) $food["stock"] < 1): ?>

                                <span class="badge text-bg-danger mb-2 align-self-start">
                                    <i class="fa-solid fa-box-open me-1"></i>Out of Stock
                                </span>

                            <?php elseif ((int) $food["stock"] <= 5): ?>

                                <span class="badge text-bg-warning mb-2 align-self-start">
                                    <i class="fa-solid fa-box me-1"></i>Only <?= (int) $food["stock"] ?> left in stock
                                </span>

                            <?php else: ?>

                                <small class="text-success mb-2">
                                    <i class="fa-solid fa-box me-1"></i>In stock: <?= (int) $food["stock"] ?>
                                </small>

                            <?php endif; ?>

                            <?php if ($isLoggedIn): ?>

                                <?php if ((int) $food["stock"] > 0): ?>

                                    <form action="add_to_cart.php" method="POST">

                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                                        <input type="hidden" name="food_id" value="<?= (int) $food["id"] ?>">

                                        <div class="d-flex gap-2">

                                            <input
                                                type="number"
                                                name="quantity"
                                                class="form-control form-control-sm qty-input"
                                                value="1"
                                                min="1"
                                                max="<?= (int) $food["stock"] ?>"
                                            >

                                            <button type="submit" class="btn btn-danger btn-sm flex-grow-1">
                                                <i class="fa-solid fa-cart-plus me-1"></i>Add
                                            </button>

                                        </div>

                                    </form>

                                <?php else: ?>

                                    <button type="button" class="btn btn-secondary btn-sm" disabled>
                                        <i class="fa-solid fa-ban me-1"></i>Order Not Available
                                    </button>

                                <?php endif; ?>

                            <?php else: ?>

                                <a href="../auth/login.php" class="btn btn-outline-danger btn-sm">
                                    <i class="fa-solid fa-right-to-bracket me-1"></i>Login to Order
                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fa-solid fa-box-open fa-3x text-muted mb-3"></i>

                <h5>No products found</h5>

                <p class="text-muted">Try a different search or category.</p>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>