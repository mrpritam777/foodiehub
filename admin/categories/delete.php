<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!$id) {
    $_SESSION["error"] = "Invalid category ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: index.php");
    exit;
}

try {

    $categoryStatement = $conn->prepare(
        "SELECT id, category_name
         FROM categories
         WHERE id = :id
         LIMIT 1"
    );

    $categoryStatement->execute([
        ":id" => $id
    ]);

    $category = $categoryStatement->fetch(PDO::FETCH_ASSOC);

    if (!$category) {
        $_SESSION["error"] = "Category not found.";
        header("Location: index.php");
        exit;
    }

    $foodCountStatement = $conn->prepare(
        "SELECT COUNT(*)
         FROM foods
         WHERE category_id = :category_id"
    );

    $foodCountStatement->execute([
        ":category_id" => $id
    ]);

    $foodCount = (int) $foodCountStatement->fetchColumn();

    if ($foodCount > 0) {
        $_SESSION["error"] =
            "This category cannot be deleted because it contains " .
            $foodCount .
            " food item(s).";

        header("Location: index.php");
        exit;
    }

    $deleteStatement = $conn->prepare(
        "DELETE FROM categories
         WHERE id = :id"
    );

    $deleteStatement->execute([
        ":id" => $id
    ]);

    $_SESSION["success"] = "Category deleted successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to delete category. Please try again.";

    header("Location: index.php");
    exit;
}