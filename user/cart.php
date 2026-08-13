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
         foods.status
     FROM cart
     INNER JOIN foods ON foods.id = cart.food_id
     WHERE cart.user_id = :user_id
     ORDER BY cart.id DESC"
);

$stmt->execute([":user_id" => (int) $_SESSION["user_id"]]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAmount = 0.0;

foreach ($cartItems as $item) {
    $totalAmount += (float) $item["price"] * (int) $item["quantity"];
}

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);
?>

<div class="container py-4">

    <?php if ($flash !== ""): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-1"></i>
            <?= htmlspecialchars($flash) ?>

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
                                                    src="../uploads/foods/<?= htmlspecialchars($item["image"]) ?>"
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
                                            <?php endif; ?>
                                        </td>

                                        <td>₹<?= number_format((float) $item["price"], 2) ?></td>

                                        <td>

                                            <form action="update_cart.php" method="POST" class="d-flex align-items-center gap-1">

                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                                                <input type="hidden" name="cart_id" value="<?= (int) $item["cart_id"] ?>">

                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    class="form-control form-control-sm qty-input"
                                                    value="<?= (int) $item["quantity"] ?>"
                                                    min="1"
                                                    max="99"
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

                        <a href="checkout.php" class="btn btn-danger w-100">
                            <i class="fa-solid fa-cash-register me-1"></i>Proceed to Checkout
                        </a>

                    </div>

                </div>

            </div>

        </div>

    <?php else: ?>

        <div class="card shadow-sm">

            <div class="card-body text-center py-5">

                <i class="fa-solid fa-cart-shopping fa-3x text-muted mb-3"></i>

                <h5>Your cart is empty</h5>

                <p class="text-muted">Browse the menu and add your favorite dishes.</p>

                <a href="index.php" class="btn btn-danger">Browse Menu</a>

            </div>

        </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>