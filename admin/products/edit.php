<?php

$pageTitle = "Edit Product";
$activePage = "products";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION["error"] = "Invalid product ID.";
    header("Location: index.php");
    exit;
}

$foodStatement = $conn->prepare(
    "SELECT id, category_id, food_name, price, stock, description, image, status
     FROM foods
     WHERE id = :id
     LIMIT 1"
);

$foodStatement->execute([
    ":id" => $id
]);

$food = $foodStatement->fetch(PDO::FETCH_ASSOC);

if (!$food) {
    $_SESSION["error"] = "Product not found.";
    header("Location: index.php");
    exit;
}

$categoryStatement = $conn->query(
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

$csrfToken = generateCsrfToken();

$errorMessage = $_SESSION["error"] ?? "";
$successMessage = $_SESSION["success"] ?? "";
$old = $_SESSION["food_old"] ?? [];

unset(
    $_SESSION["error"],
    $_SESSION["success"],
    $_SESSION["food_old"]
);

$foodName = $old["food_name"] ?? $food["food_name"];
$categoryId = $old["category_id"] ?? $food["category_id"];
$price = $old["price"] ?? $food["price"];
$stock = $old["stock"] ?? $food["stock"];
$description = $old["description"] ?? $food["description"];
$status = $old["status"] ?? $food["status"];
?>

<div class="d-flex">

    <?php require_once __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content flex-grow-1">

        <?php require_once __DIR__ . "/../../includes/admin_navbar.php"; ?>

        <div class="container-fluid py-4">

            <div class="row justify-content-center">

                <div class="col-xl-9 col-lg-10">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                            <h3 class="mb-1">Edit Product</h3>
                            <p class="text-muted mb-0">
                                Update product details, stock and availability.
                            </p>
                        </div>

                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Back
                        </a>

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

                    <div class="card shadow-sm">

                        <div class="card-body p-4">

                            <form
                                action="update.php"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $food["id"] ?>"
                                >

                                <div class="row g-4">

                                    <div class="col-md-6">

                                        <label for="food_name" class="form-label fw-semibold">
                                            Product Name <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="food_name"
                                            id="food_name"
                                            class="form-control"
                                            placeholder="Example: Chicken Roll"
                                            maxlength="150"
                                            value="<?= htmlspecialchars($foodName) ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-md-6">

                                        <label for="category_id" class="form-label fw-semibold">
                                            Category <span class="text-danger">*</span>
                                        </label>

                                        <select name="category_id" id="category_id" class="form-select" required>

                                            <option value="">Select Category</option>

                                            <?php foreach ($categories as $category): ?>

                                                <option
                                                    value="<?= (int) $category["id"] ?>"
                                                    <?= (string) $categoryId === (string) $category["id"] ? "selected" : "" ?>
                                                >
                                                    <?= htmlspecialchars($category["category_name"]) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="price" class="form-label fw-semibold">
                                            Price <span class="text-danger">*</span>
                                        </label>

                                        <div class="input-group">

                                            <span class="input-group-text">₹</span>

                                            <input
                                                type="number"
                                                name="price"
                                                id="price"
                                                class="form-control"
                                                min="0"
                                                step="0.01"
                                                placeholder="0.00"
                                                value="<?= htmlspecialchars($price) ?>"
                                                required
                                            >

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="stock" class="form-label fw-semibold">
                                            Stock Quantity <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            name="stock"
                                            id="stock"
                                            class="form-control"
                                            min="0"
                                            max="999999"
                                            step="1"
                                            value="<?= htmlspecialchars($stock) ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-md-6">

                                        <label for="status" class="form-label fw-semibold">
                                            Availability <span class="text-danger">*</span>
                                        </label>

                                        <select name="status" id="status" class="form-select" required>

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

                                    <div class="col-12">

                                        <label for="description" class="form-label fw-semibold">
                                            Description
                                        </label>

                                        <textarea
                                            name="description"
                                            id="description"
                                            class="form-control"
                                            rows="5"
                                            maxlength="2000"
                                            placeholder="Write food description..."
                                        ><?= htmlspecialchars($description) ?></textarea>

                                    </div>

                                    <div class="col-12">

                                        <label for="image" class="form-label fw-semibold">Product Image</label>

                                        <?php if (!empty($food["image"])): ?>

                                            <div class="mb-2">

                                                <img
                                                    src="../../uploads/products/<?= htmlspecialchars($food["image"]) ?>"
                                                    alt="<?= htmlspecialchars($food["food_name"]) ?>"
                                                    width="120"
                                                    height="90"
                                                    class="rounded object-fit-cover border"
                                                >

                                                <div class="form-check mt-2">

                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        name="remove_image"
                                                        id="remove_image"
                                                        value="1"
                                                    >

                                                    <label class="form-check-label" for="remove_image">
                                                        Remove current image
                                                    </label>

                                                </div>

                                            </div>

                                        <?php endif; ?>

                                        <input
                                            type="file"
                                            name="image"
                                            id="image"
                                            class="form-control"
                                            accept=".jpg,.jpeg,.png,.webp"
                                        >

                                        <div class="form-text">
                                            JPG, JPEG, PNG or WEBP. Max 2 MB. Leave empty to keep current image.
                                        </div>

                                    </div>

                                    <div class="col-12">

                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="fa-solid fa-pen-to-square me-1"></i>
                                            Update Product
                                        </button>

                                        <a href="index.php" class="btn btn-light border px-4">
                                            Cancel
                                        </a>

                                    </div>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<?php require_once __DIR__ . "/../../includes/admin_footer.php"; ?>