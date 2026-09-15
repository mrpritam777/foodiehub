<?php

require_once __DIR__ . "/config/database.php";

$uploadDirectory = __DIR__ . "/uploads/products/";

$productRenames = [
    1  => "Remote Control Car",
    2  => "Building Blocks Set",
    3  => "Ceiling Fan 56 inch",
    4  => "Table Fan 16 inch",
    5  => "Smart LED TV 43 inch",
    8  => "Split AC 1.5 Ton",
    9  => "Window AC 1 Ton",
    10 => "Android TV Box",
];

// 2) Old food photos that must be removed (id => reason)
$removeImageFor = [1, 2, 3, 4, 5, 8, 9, 10];

try {

    $conn->beginTransaction();

    $renameStatement = $conn->prepare(
        "UPDATE foods SET food_name = :name WHERE id = :id"
    );

    foreach ($productRenames as $id => $newName) {
        $renameStatement->execute([":name" => $newName, ":id" => $id]);
        echo "Renamed product #{$id} -> {$newName}\n";
    }

    // 3) Remove the old food photos
    $imageStatement = $conn->prepare(
        "SELECT id, food_name, image FROM foods WHERE id = :id"
    );

    $clearImageStatement = $conn->prepare(
        "UPDATE foods SET image = NULL WHERE id = :id"
    );

    foreach ($removeImageFor as $id) {

        $imageStatement->execute([":id" => $id]);
        $product = $imageStatement->fetch(PDO::FETCH_ASSOC);

        if ($product && !empty($product["image"])) {

            $imagePath = $uploadDirectory . $product["image"];

            if (is_file($imagePath)) {
                unlink($imagePath);
                echo "Deleted old food image: " . $product["image"] . "\n";
            } else {
                echo "Food image not found on disk (skipped): " . $product["image"] . "\n";
            }

            $clearImageStatement->execute([":id" => $id]);
        }
    }

    $conn->commit();

    // 4) Rename the remaining product images: food_* -> product_*
    $rows = $conn->query(
        "SELECT id, food_name, image FROM foods WHERE image IS NOT NULL"
    );

    $updateImageStatement = $conn->prepare(
        "UPDATE foods SET image = :image WHERE id = :id"
    );

    foreach ($rows as $row) {

        $oldName = $row["image"];

        if (strpos($oldName, "food_") === 0) {

            $newName = "product_" . substr($oldName, strlen("food_"));
            $oldPath = $uploadDirectory . $oldName;
            $newPath = $uploadDirectory . $newName;

            if (is_file($oldPath)) {

                rename($oldPath, $newPath);

                $updateImageStatement->execute([":image" => $newName, ":id" => $row["id"]]);

                echo "Renamed image for #{$row['id']} ({$row['food_name']}): {$oldName} -> {$newName}\n";

            } else {
                echo "Image file missing on disk (kept db value): {$oldName}\n";
            }
        }
    }

    echo "\n=== Final product list ===\n";

    foreach ($conn->query("SELECT id, food_name, image, stock, status FROM foods ORDER BY id") as $row) {
        echo "#" . $row["id"]
           . "  " . str_pad($row["food_name"], 24)
           . "  img=" . str_pad($row["image"] ?? "NULL", 18)
           . "  stock=" . str_pad($row["stock"], 3)
           . "  " . $row["status"] . "\n";
    }

    echo "\nConversion complete.\n";

} catch (PDOException $exception) {

    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    die("Conversion failed: " . $exception->getMessage() . "\n");
}
