<?php

$pageTitle = "Register";
$cardTitle = "Create Account";
$cardSubtitle = "Join FoodieHub to start ordering food";

require_once __DIR__ . "/auth_header.php";
?>

<form action="register_process.php" method="POST" autocomplete="off">

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

    <div class="mb-3">

        <label class="form-label fw-semibold">Full Name</label>

        <input
            type="text"
            name="name"
            class="form-control"
            placeholder="John Doe"
            maxlength="100"
            value="<?= htmlspecialchars($_SESSION["old_name"] ?? "") ?>"
            required
        >

    </div>

    <div class="mb-3">

        <label class="form-label fw-semibold">Email Address</label>

        <input
            type="email"
            name="email"
            class="form-control"
            placeholder="you@example.com"
            maxlength="150"
            value="<?= htmlspecialchars($_SESSION["old_email"] ?? "") ?>"
            required
        >

    </div>

    <div class="mb-3">

        <label class="form-label fw-semibold">Phone</label>

        <input
            type="text"
            name="phone"
            class="form-control"
            placeholder="019xxxxxxxx"
            maxlength="20"
            value="<?= htmlspecialchars($_SESSION["old_phone"] ?? "") ?>"
            required
        >

    </div>

    <div class="mb-4">

        <label class="form-label fw-semibold">Password</label>

        <input
            type="password"
            name="password"
            class="form-control"
            placeholder="Minimum 6 characters"
            minlength="6"
            required
        >

    </div>

    <button class="btn btn-danger w-100 py-2">
        <i class="fa-solid fa-user-plus me-1"></i>Register
    </button>

</form>

<p class="text-center mt-4 mb-0">
    Already have an account?
    <a href="login.php" class="fw-semibold text-danger">Login here</a>
</p>

<?php
unset($_SESSION["old_name"], $_SESSION["old_email"], $_SESSION["old_phone"]);
require_once __DIR__ . "/auth_footer.php";
?>