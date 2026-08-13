<?php

require_once __DIR__ . "/../includes/user_auth.php";
require_once __DIR__ . "/../includes/user_header.php";

$flash = $_SESSION["flash"] ?? "";
unset($_SESSION["flash"]);

$flashError = $_SESSION["flash_error"] ?? "";
unset($_SESSION["flash_error"]);

$old = $_SESSION["profile_old"] ?? [];
unset($_SESSION["profile_old"]);

$stmt = $conn->prepare(
    "SELECT id, name, email, phone
     FROM users
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([":id" => (int) $_SESSION["user_id"]]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: ../auth/logout.php");
    exit;
}

$name = $old["name"] ?? $user["name"];
$email = $old["email"] ?? $user["email"];
$phone = $old["phone"] ?? $user["phone"];
?>

<div class="container py-4">

    <div class="row justify-content-center">

        <div class="col-lg-6 col-md-8">

            <div class="d-flex justify-content-between align-items-center mb-4">

                <h2 class="mb-0">My Profile</h2>

                <a href="index.php" class="btn btn-outline-danger">
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

                    <form action="update_profile.php" method="POST">

                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">

                        <div class="mb-3">

                            <label class="form-label fw-semibold">Full Name</label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?= htmlspecialchars($name) ?>"
                                maxlength="100"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label fw-semibold">Email</label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?= htmlspecialchars($email) ?>"
                                maxlength="150"
                                required
                            >

                        </div>

                        <div class="mb-4">

                            <label class="form-label fw-semibold">Phone</label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?= htmlspecialchars($phone ?? "") ?>"
                                maxlength="20"
                            >

                        </div>

                        <div class="d-flex gap-2">

                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Update Profile
                            </button>

                            <a href="change_password.php" class="btn btn-light border px-4">
                                <i class="fa-solid fa-key me-1"></i>Change Password
                            </a>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once __DIR__ . "/../includes/user_footer.php"; ?>