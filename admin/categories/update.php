<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;
$categoryName = trim($_POST["category_name"] ?? "");

if (!$id) {
    $_SESSION["error"] = "Invalid category ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: edit.php?id=" . $id);
    exit;
}

$_SESSION["old_category_name"] = $categoryName;

if ($categoryName === "") {
    $_SESSION["error"] = "Category name is required.";
    header("Location: edit.php?id=" . $id);
    exit;
}

$nameLength = mb_strlen($categoryName);

if ($nameLength < 2 || $nameLength > 100) {
    $_SESSION["error"] = "Category name must contain between 2 and 100 characters.";
    header("Location: edit.php?id=" . $id);
    exit;
}

try {

    $categoryStatement = $conn->prepare(
        "SELECT id
         FROM categories
         WHERE id = :id
         LIMIT 1"
    );

    $categoryStatement->execute([
        ":id" => $id
    ]);

    if (!$categoryStatement->fetch(PDO::FETCH_ASSOC)) {
        unset($_SESSION["old_category_name"]);

        $_SESSION["error"] = "Category not found.";
        header("Location: index.php");
        exit;
    }

    $duplicateStatement = $conn->prepare(
        "SELECT id
         FROM categories
         WHERE LOWER(category_name) = LOWER(:category_name)
         AND id != :id
         LIMIT 1"
    );

    $duplicateStatement->execute([
        ":category_name" => $categoryName,
        ":id" => $id
    ]);

    if ($duplicateStatement->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["error"] = "Another category with this name already exists.";
        header("Location: edit.php?id=" . $id);
        exit;
    }

    $updateStatement = $conn->prepare(
        "UPDATE categories
         SET category_name = :category_name
         WHERE id = :id"
    );

    $updateStatement->execute([
        ":category_name" => $categoryName,
        ":id" => $id
    ]);

    unset($_SESSION["old_category_name"]);

    $_SESSION["success"] = "Category updated successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to update category. Please try again.";

    header("Location: edit.php?id=" . $id);
    exit;
}