<nav class="navbar navbar-light bg-white shadow-sm">

    <div class="container-fluid">

        <h4 class="mb-0">
            <?= htmlspecialchars($pageTitle ?? "Dashboard") ?>
        </h4>

        <div>
            Welcome,
            <strong>
                <?= htmlspecialchars($_SESSION['admin_name']) ?>
            </strong>
        </div>

    </div>

</nav>