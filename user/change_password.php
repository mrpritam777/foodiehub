<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

$flashError = $_SESSION["flash_error"] ?? "";
unset($_SESSION["flash_error"]);
?>

<div class="container py-4">

    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2 class="mb-0">Change Password</h2>

                <a href="profile.php" class="btn btn-outline-danger">
                    <i class="fa-solid fa-arrow-left me-1"></i>Back
                </a>

            </div>

            <?php if ($flashError !== ""): ?>

                <div class="alert alert-danger alert-dismissible fade show">

                    <i class="fa-solid fa-circle-exclamation me-1"></i>
                    <?= htmlspecialchars($flashError) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php endif; ?>

            <?php if ($flash !== ""): ?>

                <div class="alert alert-success alert-dismissible fade show">

                    <i class="fa-solid fa-circle-check me-1"></i>
                    <?= htmlspecialchars($flash) ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

                </div>

            <?php endif; ?>

            <div class="card shadow-sm">

                <div class="card-body p-4">

                    <form action="change_password_process.php" method="POST">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">Current Password</label>

                            <input
                                type="password"
                                name="current_password"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">New Password</label>

                            <input
                                type="password"
                                name="new_password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                            <div class="form-text">Minimum 6 characters.</div>

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">Confirm New Password</label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>

                        <button type="submit" class="btn btn-danger px-4">
                            <i class="fa-solid fa-key me-1"></i>Update Password
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>