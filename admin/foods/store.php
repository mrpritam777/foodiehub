<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$csrfToken = $_POST["csrf_token"] ?? null;

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: create.php");
    exit;
}

$foodName = trim($_POST["food_name"] ?? "");
$categoryId = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);
$priceInput = trim($_POST["price"] ?? "");
$description = trim($_POST["description"] ?? "");
$status = trim($_POST["status"] ?? "");

$_SESSION["food_old"] = [
    "food_name" => $foodName,
    "category_id" => $categoryId,
    "price" => $priceInput,
    "description" => $description,
    "status" => $status
];

$allowedStatuses = ["Available", "Unavailable"];

if ($foodName === "") {
    $_SESSION["error"] = "Food name is required.";
    header("Location: create.php");
    exit;
}

if (mb_strlen($foodName) < 2 || mb_strlen($foodName) > 150) {
    $_SESSION["error"] = "Food name must contain between 2 and 150 characters.";
    header("Location: create.php");
    exit;
}

if (!$categoryId) {
    $_SESSION["error"] = "Please select a valid category.";
    header("Location: create.php");
    exit;
}

if (
    $priceInput === "" ||
    !is_numeric($priceInput) ||
    (float) $priceInput < 0
) {
    $_SESSION["error"] = "Please enter a valid food price.";
    header("Location: create.php");
    exit;
}

$price = round((float) $priceInput, 2);

if (!in_array($status, $allowedStatuses, true)) {
    $_SESSION["error"] = "Please select a valid availability status.";
    header("Location: create.php");
    exit;
}

if (mb_strlen($description) > 2000) {
    $_SESSION["error"] = "Description cannot exceed 2000 characters.";
    header("Location: create.php");
    exit;
}

$categoryStatement = $conn->prepare(
    "SELECT id
     FROM categories
     WHERE id = :id
     LIMIT 1"
);

$categoryStatement->execute([
    ":id" => $categoryId
]);

if (!$categoryStatement->fetch(PDO::FETCH_ASSOC)) {
    $_SESSION["error"] = "Selected category does not exist.";
    header("Location: create.php");
    exit;
}

$imageName = null;

if (
    isset($_FILES["image"]) &&
    $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
) {
    $image = $_FILES["image"];

    if ($image["error"] !== UPLOAD_ERR_OK) {
        $_SESSION["error"] = "Food image upload failed.";
        header("Location: create.php");
        exit;
    }

    $maxFileSize = 2 * 1024 * 1024;

    if ($image["size"] > $maxFileSize) {
        $_SESSION["error"] = "Food image size cannot exceed 2 MB.";
        header("Location: create.php");
        exit;
    }

    $allowedMimeTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($image["tmp_name"]);

    if (!isset($allowedMimeTypes[$mimeType])) {
        $_SESSION["error"] = "Only JPG, PNG and WEBP images are allowed.";
        header("Location: create.php");
        exit;
    }

    $extension = $allowedMimeTypes[$mimeType];

    $imageName = "food_" . bin2hex(random_bytes(12)) . "." . $extension;

    $uploadDirectory = __DIR__ . "/../../uploads/foods/";

    if (!is_dir($uploadDirectory)) {
        if (!mkdir($uploadDirectory, 0755, true)) {
            $_SESSION["error"] = "Unable to create food upload directory.";
            header("Location: create.php");
            exit;
        }
    }

    $destination = $uploadDirectory . $imageName;

    if (!move_uploaded_file($image["tmp_name"], $destination)) {
        $_SESSION["error"] = "Unable to save the uploaded food image.";
        header("Location: create.php");
        exit;
    }
}

try {

    $insertStatement = $conn->prepare(
        "INSERT INTO foods (
            category_id,
            food_name,
            price,
            description,
            image,
            status
        ) VALUES (
            :category_id,
            :food_name,
            :price,
            :description,
            :image,
            :status
        )"
    );

    $insertStatement->execute([
        ":category_id" => $categoryId,
        ":food_name" => $foodName,
        ":price" => $price,
        ":description" => $description !== "" ? $description : null,
        ":image" => $imageName,
        ":status" => $status
    ]);

    unset($_SESSION["food_old"]);

    $_SESSION["success"] = "Food item added successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    if ($imageName !== null) {
        $uploadedImage = __DIR__ . "/../../uploads/foods/" . $imageName;

        if (is_file($uploadedImage)) {
            unlink($uploadedImage);
        }
    }

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to add food item. Please try again.";

    header("Location: create.php");
    exit;
}