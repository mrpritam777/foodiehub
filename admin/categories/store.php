<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    redirectWithMessage(
        "create.php",
        "error",
        "Invalid request. Please try again."
    );
}

$categoryName = trim($_POST["category_name"] ?? "");

$_SESSION["old_category_name"] = $categoryName;

if ($categoryName === "") {
    redirectWithMessage(
        "create.php",
        "error",
        "Category name is required."
    );
}

$nameLength = mb_strlen($categoryName);

if ($nameLength < 2 || $nameLength > 100) {
    redirectWithMessage(
        "create.php",
        "error",
        "Category name must contain between 2 and 100 characters."
    );
}

try {
    $checkStatement = $conn->prepare(
        "SELECT id
         FROM categories
         WHERE LOWER(category_name) = LOWER(:category_name)
         LIMIT 1"
    );

    $checkStatement->execute([
        ":category_name" => $categoryName
    ]);

    if ($checkStatement->fetch(PDO::FETCH_ASSOC)) {
        redirectWithMessage(
            "create.php",
            "error",
            "This category already exists."
        );
    }

    $insertStatement = $conn->prepare(
        "INSERT INTO categories (category_name)
         VALUES (:category_name)"
    );

    $insertStatement->execute([
        ":category_name" => $categoryName
    ]);

    unset($_SESSION["old_category_name"]);

    $_SESSION["success"] = "Category added successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {
    error_log($exception->getMessage());

    redirectWithMessage(
        "create.php",
        "error",
        "Unable to add category. Please try again."
    );
}