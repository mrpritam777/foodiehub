<?php

$pageTitle = "Edit Category";
$activePage = "categories";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION["error"] = "Invalid category ID.";
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id, category_name
     FROM categories
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    ":id" => $id
]);

$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    $_SESSION["error"] = "Category not found.";
    header("Location: index.php");
    exit;
}

$csrfToken = generateCsrfToken();

$errorMessage = $_SESSION["error"] ?? "";
$oldCategoryName = $_SESSION["old_category_name"] ?? $category["category_name"];

unset(
    $_SESSION["error"],
    $_SESSION["old_category_name"]
);
?>

<div class="d-flex">

    <?php require_once __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content flex-grow-1">

        <?php require_once __DIR__ . "/../../includes/admin_navbar.php"; ?>

        <div class="container-fluid py-4">

            <div class="row justify-content-center">

                <div class="col-lg-7 col-xl-6">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>
                            <h3 class="mb-1">Edit Category</h3>

                            <p class="text-muted mb-0">
                                Update category information.
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

                    <?php if (!empty($errorMessage)): ?>

                        <div
                            class="alert alert-danger alert-dismissible fade show"
                            role="alert"
                        >
                            <i class="fa-solid fa-circle-exclamation me-1"></i>

                            <?= htmlspecialchars($errorMessage) ?>

                            <button
                                type="button"
                                class="btn-close"
                                data-bs-dismiss="alert"
                            ></button>
                        </div>

                    <?php endif; ?>

                    <div class="card shadow-sm">

                        <div class="card-body p-4">

                            <form
                                action="update.php"
                                method="POST"
                                autocomplete="off"
                            >

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= htmlspecialchars($csrfToken) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int) $category["id"] ?>"
                                >

                                <div class="mb-4">

                                    <label
                                        for="category_name"
                                        class="form-label fw-semibold"
                                    >
                                        Category Name
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="category_name"
                                        id="category_name"
                                        class="form-control form-control-lg"
                                        value="<?= htmlspecialchars($oldCategoryName) ?>"
                                        placeholder="Enter category name"
                                        maxlength="100"
                                        required
                                    >

                                    <div class="form-text">
                                        Category name must contain between 2 and 100 characters.
                                    </div>

                                </div>

                                <div class="d-flex gap-2">

                                    <button
                                        type="submit"
                                        name="update_category"
                                        class="btn btn-primary px-4"
                                    >
                                        <i class="fa-solid fa-pen-to-square me-1"></i>
                                        Update Category
                                    </button>

                                    <a
                                        href="index.php"
                                        class="btn btn-light border px-4"
                                    >
                                        Cancel
                                    </a>

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