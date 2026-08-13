<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

// Fetch the logged-in user's details to pre-fill the form
$userStatement = $conn->prepare(
    "SELECT name, email, phone FROM users WHERE id = :id LIMIT 1"
);

$userStatement->execute([":id" => (int) $_SESSION["user_id"]]);

$userData = $userStatement->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare(
    "SELECT
         cart.id     AS cart_id,
         cart.quantity,
         foods.id     AS food_id,
         foods.food_name,
         foods.price,
         foods.status
     FROM cart
     INNER JOIN foods ON foods.id = cart.food_id
     WHERE cart.user_id = :user_id
     ORDER BY cart.id DESC"
);

$stmt->execute([":user_id" => (int) $_SESSION["user_id"]]);

$cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalAmount = 0.0;
$hasUnavailable = false;

foreach ($cartItems as $item) {
    if ($item["status"] !== "Available") {
        $hasUnavailable = true;
    }
    $totalAmount += (float) $item["price"] * (int) $item["quantity"];
}

$totalAmount = round($totalAmount, 2);

if (count($cartItems) === 0) {
    $_SESSION["flash"] = "Your cart is empty.";
    header("Location: cart.php");
    exit;
}

$finalTotal = round($totalAmount, 2);

$flash = $_SESSION["flash"] ?? "";
$errorMessage = $_SESSION["error"] ?? "";
$successMessage = $_SESSION["success"] ?? "";

unset($_SESSION["flash"], $_SESSION["error"], $_SESSION["success"]);

// Old delivery input after a validation error
$old = $_SESSION["checkout_old"] ?? [];
unset($_SESSION["checkout_old"]);
?>

<div class="container py-4">

    <?php if ($flash !== ""): ?>

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fa-solid fa-circle-exclamation me-1"></i>
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

    <?php if ($successMessage !== ""): ?>

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fa-solid fa-circle-check me-1"></i>
            <?= htmlspecialchars($successMessage) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <?php if ($hasUnavailable): ?>

        <div class="alert alert-warning alert-dismissible fade show">

            <i class="fa-solid fa-triangle-exclamation me-1"></i>
            Some items in your cart are currently unavailable. Please remove them before placing the order.

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2 class="mb-0">Checkout</h2>

        <a href="cart.php" class="btn btn-outline-danger">
            <i class="fa-solid fa-arrow-left me-1"></i>Back to Cart
        </a>

    </div>

    <div class="row g-4">

        <div class="col-lg-7">

            <form action="place_order.php" method="POST" id="checkout-form">

                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                <div class="card shadow-sm mb-4">

                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="fa-solid fa-truck me-1 text-danger"></i>
                            Delivery Information
                        </h5>
                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-6">

                                <label for="customer_name" class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="customer_name"
                                    id="customer_name"
                                    class="form-control"
                                    placeholder="Your full name"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($old["customer_name"] ?? $userData["name"] ?? "") ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label for="customer_phone" class="form-label fw-semibold">
                                    Phone Number <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="tel"
                                    name="customer_phone"
                                    id="customer_phone"
                                    class="form-control"
                                    placeholder="01XXXXXXXXX"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($old["customer_phone"] ?? $userData["phone"] ?? "") ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label for="customer_email" class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="email"
                                    name="customer_email"
                                    id="customer_email"
                                    class="form-control"
                                    placeholder="you@example.com"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($old["customer_email"] ?? $userData["email"] ?? "") ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label for="city" class="form-label fw-semibold">
                                    City <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="city"
                                    id="city"
                                    class="form-control"
                                    placeholder="Example: Kolkata"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($old["city"] ?? "") ?>"
                                    required
                                >

                            </div>

                            <div class="col-12">

                                <label for="delivery_address" class="form-label fw-semibold">
                                    Delivery Address <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    name="delivery_address"
                                    id="delivery_address"
                                    class="form-control"
                                    rows="2"
                                    maxlength="500"
                                    placeholder="House, Road, Block..."
                                    required
                                ><?= htmlspecialchars($old["delivery_address"] ?? "") ?></textarea>

                            </div>

                            <div class="col-md-6">

                                <label for="area" class="form-label fw-semibold">
                                    Area / Location <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="area"
                                    id="area"
                                    class="form-control"
                                    placeholder="Example: Salt lake"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($old["area"] ?? "") ?>"
                                    required
                                >

                            </div>

                            <div class="col-md-6">

                                <label for="postal_code" class="form-label fw-semibold">
                                    Postal Code
                                </label>

                                <input
                                    type="text"
                                    name="postal_code"
                                    id="postal_code"
                                    class="form-control"
                                    placeholder="Optional"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($old["postal_code"] ?? "") ?>"
                                >

                            </div>

                            <div class="col-12">

                                <label for="order_note" class="form-label fw-semibold">
                                    Order Note / Special Instructions
                                </label>

                                <textarea
                                    name="order_note"
                                    id="order_note"
                                    class="form-control"
                                    rows="2"
                                    maxlength="1000"
                                    placeholder="Optional instructions for the delivery..."
                                ><?= htmlspecialchars($old["order_note"] ?? "") ?></textarea>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card shadow-sm">

                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Items</h5>
                    </div>

                    <div class="card-body p-0">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">
                                <tr>
                                    <th>Food</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($cartItems as $item): ?>

                                    <tr>

                                        <td>
                                            <strong><?= htmlspecialchars($item["food_name"]) ?></strong>

                                            <?php if ($item["status"] !== "Available"): ?>
                                                <span class="badge text-bg-warning ms-1">Unavailable</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>₹<?= number_format((float) $item["price"], 2) ?></td>

                                        <td><?= (int) $item["quantity"] ?></td>

                                        <td class="text-end">₹<?= number_format((float) $item["price"] * (int) $item["quantity"], 2) ?></td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </form>

        </div>

        <div class="col-lg-5">

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-white">
                    <h5 class="mb-0">Order Summary</h5>
                </div>

                <div class="card-body">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Cart Subtotal</span>
                        <span>₹<?= number_format($totalAmount, 2) ?></span>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between fw-bold fs-5 mb-3">
                        <span>Final Total</span>
                        <span>₹<?= number_format($finalTotal, 2) ?></span>
                    </div>

                    <button
                        type="submit"
                        form="checkout-form"
                        class="btn btn-danger w-100 mb-2"
                        <?= $hasUnavailable ? "disabled" : "" ?>
                    >
                        <i class="fa-solid fa-check me-1"></i>Place Order
                    </button>

                    <small class="text-muted">
                        By placing the order you agree to pay the total amount on delivery.
                    </small>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>
