<?php

$pageTitle = "Login";
$cardTitle = "Welcome Back";
$cardSubtitle = "Sign in to continue to FoodieHub";

require_once __DIR__ . "/auth_header.php";
?>

<form action="login_process.php" method="POST" autocomplete="off">

    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

    <div class="mb-3">

        <label class="form-label fw-semibold">Email Address</label>

        <div class="input-group">

            <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>

            <input type="email" name="email" class="form-control" placeholder="you@example.com" required>

        </div>

    </div>

    <div class="mb-4">

        <label class="form-label fw-semibold">Password</label>

        <div class="input-group">

            <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>

            <input type="password" name="password" class="form-control" placeholder="Enter your password" required>

        </div>

    </div>

    <button class="btn btn-danger w-100 py-2">
        <i class="fa-solid fa-right-to-bracket me-1"></i>Login
    </button>

</form>

<p class="text-center mt-4 mb-0">
    Don't have an account?
    <a href="register.php" class="fw-semibold text-danger">Register here</a>
</p>

<?php require_once __DIR__ . "/auth_footer.php"; ?>