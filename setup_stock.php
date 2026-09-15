<?php

/**
 * One-time migration: adds the `stock` column to the `foods` (products) table.
 *
 * Run from the project root:  php setup_stock.php
 * (Safe to run multiple times - it skips itself when the column exists.)
 */

require_once __DIR__ . "/config/database.php";

try {

    $columnStatement = $conn->query("SHOW COLUMNS FROM foods LIKE 'stock'");

    if ($columnStatement->fetch(PDO::FETCH_ASSOC)) {
        echo "The 'stock' column already exists in the foods table. Nothing to do.\n";
        exit;
    }

    $conn->exec(
        "ALTER TABLE foods
         ADD COLUMN stock INT UNSIGNED NOT NULL DEFAULT 0 AFTER price"
    );

    // Existing products get a starting stock so they can be ordered
    $conn->exec("UPDATE foods SET stock = 10");

    echo "Migration complete: 'stock' column added, existing products set to stock 10.\n";

    foreach ($conn->query("SELECT id, food_name, price, stock FROM foods ORDER BY id") as $row) {
        printf(
            "  #%d  %-30s  ₹%s  stock=%d\n",
            $row["id"],
            $row["food_name"],
            number_format((float) $row["price"], 2),
            (int) $row["stock"]
        );
    }

} catch (PDOException $exception) {

    die("Migration failed: " . $exception->getMessage() . "\n");
}
