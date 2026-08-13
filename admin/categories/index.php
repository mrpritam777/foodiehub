<?php
$pageTitle = "Categories";
$activePage = "categories";

require_once __DIR__ . "/../../includes/admin_header.php";
require_once __DIR__ . "/../../includes/functions.php";

// Search
$search = $_GET['search'] ?? '';

if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM categories WHERE category_name LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM categories ORDER BY id DESC");
}

$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex">

    <?php require_once("../../includes/admin_sidebar.php"); ?>

    <div class="flex-grow-1" style="margin-left:260px;">

        <?php require_once("../../includes/admin_navbar.php"); ?>

        <div class="container-fluid mt-4">

            <div class="d-flex justify-content-between align-items-center mb-3">

                <h3>Category Management</h3>

                <a href="create.php" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Add Category
                </a>

            </div>

            <form method="GET" class="row mb-3">

                <div class="col-md-4">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search Category..."
                        value="<?= htmlspecialchars($search) ?>">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-dark w-100">

                        Search

                    </button>

                </div>

            </form>

            <div class="card shadow">

                <div class="card-body">

                    <table class="table table-bordered table-hover">

                        <thead class="table-dark">

                            <tr>

                                <th width="80">ID</th>

                                <th>Category Name</th>

                                <th width="180">Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($categories) > 0): ?>

                                <?php foreach ($categories as $category): ?>

                                    <tr>

                                        <td><?= $category['id']; ?></td>

                                        <td><?= htmlspecialchars($category['category_name']); ?></td>

                                        <td>

                                            <a href="edit.php?id=<?= $category['id']; ?>"
                                                class="btn btn-warning btn-sm">

                                                <i class="fa fa-edit"></i>

                                            </a>

                                            <form
                                                action="delete.php"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $category["id"] ?>">

                                                <button
                                                    type="submit"
                                                    class="btn btn-danger btn-sm"
                                                    title="Delete Category">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="3" class="text-center text-danger">

                                        No Category Found

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once("../../includes/admin_footer.php"); ?>