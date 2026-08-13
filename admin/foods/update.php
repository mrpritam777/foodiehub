<?php

require_once __DIR__ . "/../../includes/admin_auth.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$csrfToken = $_POST["csrf_token"] ?? null;
$foodName = trim($_POST["food_name"] ?? "");
$categoryId = filter_input(INPUT_POST, "category_id", FILTER_VALIDATE_INT);
$priceInput = trim($_POST["price"] ?? "");
$description = trim($_POST["description"] ?? "");
$status = trim($_POST["status"] ?? "");
$removeImage = (int) filter_input(INPUT_POST, "remove_image", FILTER_VALIDATE_INT);

if (!$id) {
    $_SESSION["error"] = "Invalid food ID.";
    header("Location: index.php");
    exit;
}

if (!verifyCsrfToken($csrfToken)) {
    $_SESSION["error"] = "Invalid request. Please try again.";
    header("Location: edit.php?id=" . $id);
    exit;
}

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
    header("Location: edit.php?id=" . $id);
    exit;
}

if (mb_strlen($foodName) < 2 || mb_strlen($foodName) > 150) {
    $_SESSION["error"] = "Food name must contain between 2 and 150 characters.";
    header("Location: edit.php?id=" . $id);
    exit;
}

if (!$categoryId) {
    $_SESSION["error"] = "Please select a valid category.";
    header("Location: edit.php?id=" . $id);
    exit;
}

if ($priceInput === "" || !is_numeric($priceInput) || (float) $priceInput < 0) {
    $_SESSION["error"] = "Please enter a valid food price.";
    header("Location: edit.php?id=" . $id);
    exit;
}

$price = round((float) $priceInput, 2);

if (!in_array($status, $allowedStatuses, true)) {
    $_SESSION["error"] = "Please select a valid availability status.";
    header("Location: edit.php?id=" . $id);
    exit;
}

if (mb_strlen($description) > 2000) {
    $_SESSION["error"] = "Description cannot exceed 2000 characters.";
    header("Location: edit.php?id=" . $id);
    exit;
}

try {

    // Verify the food exists and fetch its current record
    $existingStatement = $conn->prepare(
        "SELECT id, image
         FROM foods
         WHERE id = :id
         LIMIT 1"
    );

    $existingStatement->execute([":id" => $id]);

    $existing = $existingStatement->fetch(PDO::FETCH_ASSOC);

    if (!$existing) {
        unset($_SESSION["food_old"]);
        $_SESSION["error"] = "Food item not found.";
        header("Location: index.php");
        exit;
    }

    // Verify the category exists
    $categoryStatement = $conn->prepare(
        "SELECT id FROM categories WHERE id = :id LIMIT 1"
    );

    $categoryStatement->execute([":id" => $categoryId]);

    if (!$categoryStatement->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION["error"] = "Selected category does not exist.";
        header("Location: edit.php?id=" . $id);
        exit;
    }

    // Decide the final image value
    $imageName = $existing["image"];

    // New image uploaded -> replace (or set) the image
    if (
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {
        $image = $_FILES["image"];

        if ($image["error"] !== UPLOAD_ERR_OK) {
            $_SESSION["error"] = "Food image upload failed.";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $maxFileSize = 2 * 1024 * 1024;

        if ($image["size"] > $maxFileSize) {
            $_SESSION["error"] = "Food image size cannot exceed 2 MB.";
            header("Location: edit.php?id=" . $id);
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
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $extension = $allowedMimeTypes[$mimeType];
        $newImageName = "food_" . bin2hex(random_bytes(12)) . "." . $extension;

        $uploadDirectory = __DIR__ . "/../../uploads/foods/";

        if (!is_dir($uploadDirectory)) {
            if (!mkdir($uploadDirectory, 0755, true)) {
                $_SESSION["error"] = "Unable to create food upload directory.";
                header("Location: edit.php?id=" . $id);
                exit;
            }
        }

        $destination = $uploadDirectory . $newImageName;

        if (!move_uploaded_file($image["tmp_name"], $destination)) {
            $_SESSION["error"] = "Unable to save the uploaded food image.";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        // Delete the old image file if it existed
        if ($imageName !== null && is_file($uploadDirectory . $imageName)) {
            unlink($uploadDirectory . $imageName);
        }

        $imageName = $newImageName;

    } elseif ($removeImage === 1 && $imageName !== null) {

        // User requested removal of the current image
        $uploadDirectory = __DIR__ . "/../../uploads/foods/";

        if (is_file($uploadDirectory . $imageName)) {
            unlink($uploadDirectory . $imageName);
        }

        $imageName = null;
    }

    $updateStatement = $conn->prepare(
        "UPDATE foods
         SET category_id   = :category_id,
             food_name     = :food_name,
             price         = :price,
             description   = :description,
             image         = :image,
             status        = :status
         WHERE id          = :id"
    );

    $updateStatement->execute([
        ":category_id" => $categoryId,
        ":food_name" => $foodName,
        ":price" => $price,
        ":description" => $description !== "" ? $description : null,
        ":image" => $imageName,
        ":status" => $status,
        ":id" => $id
    ]);

    unset($_SESSION["food_old"]);

    $_SESSION["success"] = "Food item updated successfully.";

    header("Location: index.php");
    exit;

} catch (PDOException $exception) {

    error_log($exception->getMessage());

    $_SESSION["error"] = "Unable to update food item. Please try again.";

    header("Location: edit.php?id=" . $id);
    exit;
}