<?php

$pageTitle = "Add Product";
$activePage = "products";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$categoryStatement = $conn->query(
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

$categories = $categoryStatement->fetchAll(PDO::FETCH_ASSOC);

$csrfToken = generateCsrfToken();

$old = $_SESSION["food_old"] ?? [];
$errorMessage = $_SESSION["error"] ?? "";

unset($_SESSION["food_old"], $_SESSION["error"]);
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
                            <h3 class="mb-1">Add Product</h3>
                            <p class="text-muted mb-0">
                                Add a new product to the store.
                            </p>
                        </div>

                        <a
                            href="index.php"
                            class="btn btn-outline-secondary"
                        >
                            <i class="fa-solid fa-arrow-left me-1"></i>
                            Back
                        </a>

                    </div>

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

                    <?php if (count($categories) === 0): ?>

                        <div class="alert alert-warning">

                          

                            <a href="../categories/create.php" class="alert-link">
                                Add Category
                            </a>

                        </div>

                    <?php endif; ?>

                    <div class="card shadow-sm">

                        <div class="card-body p-4">

                            <form
                                action="store.php"
                                method="POST"
                                enctype="multipart/form-data"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <div class="row g-4">

                                    <div class="col-md-6">

                                        <label for="food_name" class="form-label fw-semibold">
                                            Product Name
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            name="food_name"
                                            id="food_name"
                                            class="form-control"
                                            placeholder="Example: Chicken Roll"
                                            maxlength="150"
                                            value="<?= htmlspecialchars($old["food_name"] ?? "") ?>"
                                            required
                                        >

                                    </div>

                                    <div class="col-md-6">

                                        <label for="category_id" class="form-label fw-semibold">
                                            Category
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="category_id"
                                            id="category_id"
                                            class="form-select"
                                            required
                                        >

                                            <option value="">Select Category</option>

                                            <?php foreach ($categories as $category): ?>

                                                <option
                                                    value="<?= (int) $category["id"] ?>"
                                                    <?= (string) ($old["category_id"] ?? "") === (string) $category["id"]
                                                        ? "selected"
                                                        : "" ?>
                                                >
                                                    <?= htmlspecialchars($category["category_name"]) ?>
                                                </option>

                                            <?php endforeach; ?>

                                        </select>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="price" class="form-label fw-semibold">
                                            Price
                                            <span class="text-danger">*</span>
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
                                                value="<?= htmlspecialchars($old["price"] ?? "") ?>"
                                                required
                                            >

                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="stock" class="form-label fw-semibold">
                                            Stock Quantity
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="number"
                                            name="stock"
                                            id="stock"
                                            class="form-control"
                                            min="0"
                                            max="999999"
                                            step="1"
                                            placeholder="Example: 10"
                                            value="<?= htmlspecialchars($old["stock"] ?? "") ?>"
                                            required
                                        >

                                        <div class="form-text">
                                            
                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <label for="status" class="form-label fw-semibold">
                                            Availability
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="status"
                                            id="status"
                                            class="form-select"
                                            required
                                        >

                                            <option
                                                value="Available"
                                                <?= ($old["status"] ?? "Available") === "Available"
                                                    ? "selected"
                                                    : "" ?>
                                            >
                                                Available
                                            </option>

                                            <option
                                                value="Unavailable"
                                                <?= ($old["status"] ?? "") === "Unavailable"
                                                    ? "selected"
                                                    : "" ?>
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
                                            placeholder="Write product description..."
                                        ><?= htmlspecialchars($old["description"] ?? "") ?></textarea>

                                    </div>

                                    <div class="col-12">

                                        <label for="image" class="form-label fw-semibold">
                                            Product Image
                                        </label>

                                        <input
                                            type="file"
                                            name="image"
                                            id="image"
                                            class="form-control"
                                            accept=".jpg,.jpeg,.png,.webp"
                                        >

                                        <div class="form-text">
                                            JPG, JPEG, PNG and WEBP। higest 2 MB।
                                        </div>

                                    </div>

                                    <div class="col-12">

                                        <button
                                            type="submit"
                                            class="btn btn-primary px-4"
                                            <?= count($categories) === 0 ? "disabled" : "" ?>
                                        >
                                            <i class="fa-solid fa-floppy-disk me-1"></i>
                                            Save Product
                                        </button>

                                        <a
                                            href="index.php"
                                            class="btn btn-light border px-4"
                                        >
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