<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;

if (!$id) {
    $_SESSION["error"] = "Invalid food ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: index.php");
    exit;
}

try {

    $foodStatement = $conn->prepare(
        "SELECT id, image
         FROM foods
         WHERE id = :id
         LIMIT 1"
    );

    $foodStatement->execute([":id" => $id]);

    $food = $foodStatement->fetch(PDO::FETCH_ASSOC);

    if (!$food) {
        $_SESSION["error"] = "Food item not found.";
        header("Location: index.php");
        exit;
    }

    $deleteStatement = $conn->prepare("DELETE FROM foods WHERE id = :id");
    $deleteStatement->execute([":id" => $id]);

    // Remove the associated image file
    if ($food["image"] !== null) {
        $uploadDirectory = __DIR__ . "/../../uploads/foods/";

        if (is_file($uploadDirectory . $food["image"])) {
            unlink($uploadDirectory . $food["image"]);
        }
    }

    $_SESSION["success"] = "Food item deleted successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to delete food item. Please try again.";

    header("Location: index.php");
    exit;
}