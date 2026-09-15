<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$stmt = $conn->prepare(
    "SELECT
         cart.id    AS cart_id,
         cart.quantity,
         foods.id    AS food_id,
         foods.food_name,
         foods.price,
         foods.image,
         foods.status,
         foods.stock
     FROM cart
     INNER JOIN foods ON foods.id = cart.food_id
     WHERE cart.user_id = :user_id
     ORDER BY cart.id DESC"
);

$stmt->execute([":user_id" => (int) $_SESSION["user_id"]]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAmount = 0.0;
$hasStockIssue = false;

foreach ($cartItems as $item) {
    $totalAmount += (float) $item["price"] * (int) $item["quantity"];

    // Not enough stock (product unavailable or quantity above the stock)
    if ($item["status"] !== "Available" || (int) $item["stock"] < 1 || (int) $item["quantity"] > (int) $item["stock"]) {
        $hasStockIssue = true;
    }
}

$flash = $_SESSION["flash"] ?? "";
$errorMessage = $_SESSION["error"] ?? "";
unset($_SESSION["flash"], $_SESSION["error"]);
?>

<div class="container py-4">

    <?php if ($flash !== ""): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-1"></i>
            <?= htmlspecialchars($flash) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <?php if ($errorMessage !== ""): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fa-solid fa-circle-exclamation me-1"></i>
            <?= htmlspecialchars($errorMessage) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <?php if ($hasStockIssue): ?>

        <div class="alert alert-warning alert-dismissible fade show">

            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Some products in your cart do not have enough stock. Reduce the quantity or remove those products before checkout.

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="mb-0">Your Cart</h2>

        <a href="index.php" class="btn btn-outline-danger">
            <i class="fa-solid fa-arrow-left me-1"></i>Continue Shopping
        </a>

    </div>

    <?php if (count($cartItems) > 0): ?>

        <div class="row g-4">

            <div class="col-lg-8">

                <div class="card shadow-sm">

                    <div class="card-body p-0">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Food</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($cartItems as $item): ?>

                                    <tr>

                                        <td>
                                            <?php if (!empty($item["image"])): ?>
                                                <img
                                                    src="../uploads/products/<?= htmlspecialchars($item["image"]) ?>"
                                                    alt="<?= htmlspecialchars($item["food_name"]) ?>"
                                                    width="60"
                                                    height="50"
                                                    class="rounded object-fit-cover"
                                                >
                                            <?php else: ?>
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" style="width:60px;height:50px;">
                                                    <i class="fa-solid fa-burger text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <strong><?= htmlspecialchars($item["food_name"]) ?></strong>
                                            <?php if ($item["status"] === "Unavailable"): ?>
                                                <br><span class="badge text-bg-warning">Currently Unavailable</span>
                                            <?php elseif ((int) $item["stock"] < 1): ?>
                                                <br><span class="badge text-bg-danger">Out of Stock</span>
                                            <?php elseif ((int) $item["quantity"] > (int) $item["stock"]): ?>
                                                <br><span class="badge text-bg-warning">Only <?= (int) $item["stock"] ?> in stock</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>₹<?= number_format((float) $item["price"], 2) ?></td>

                                        <td>
                                            <small class="text-muted d-block mb-1">
                                                In stock: <?= (int) $item["stock"] ?>
                                            </small>

                                            <form action="update_cart.php" method="POST" class="d-flex align-items-center gap-1">

                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                                                <input type="hidden" name="cart_id" value="<?= (int) $item["cart_id"] ?>">

                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    class="form-control form-control-sm qty-input"
                                                    value="<?= (int) $item["quantity"] ?>"
                                                    min="1"
                                                    max="<?= max((int) $item["stock"], 1) ?>"
                                                >

                                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Update">
                                                    <i class="fa-solid fa-rotate"></i>
                                                </button>

                                            </form>

                                        </td>

                                        <td>₹<?= number_format((float) $item["price"] * (int) $item["quantity"], 2) ?></td>

                                        <td>

                                            <form
                                                action="remove_from_cart.php"
                                                method="POST"
                                                onsubmit="return confirm('Remove this item from cart?')"
                                            >

                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                                                <input type="hidden" name="cart_id" value="<?= (int) $item["cart_id"] ?>">

                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <div class="col-lg-4">

                <div class="card shadow-sm">

                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>

                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Items</span>
                            <span><?= count($cartItems) ?></span>
                        </div>

                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span>₹<?= number_format($totalAmount, 2) ?></span>
                        </div>

                        <hr>

                        <?php if ($hasStockIssue): ?>

                            <button type="button" class="btn btn-secondary w-100 mb-2" disabled title="Not enough stock">
                                <i class="fa-solid fa-ban me-1"></i>Checkout Blocked (Stock Issue)
                            </button>

                            <small class="text-danger d-block mb-2">
                                <i class="fa-solid fa-circle-exclamation me-1"></i>
                                Order is not possible while the cart exceeds the available stock.
                            </small>

                        <?php else: ?>

                            <a href="checkout.php" class="btn btn-danger w-100">
                                <i class="fa-solid fa-cash-register me-1"></i>Proceed to Checkout
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fa-solid fa-cart-shopping fa-3x text-muted mb-3"></i>

                <h5>Your cart is empty</h5>

                <p class="text-muted">Browse the products and add them to your cart.</p>

                <a href="index.php" class="btn btn-danger">Browse Menu</a>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>