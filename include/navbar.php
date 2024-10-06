<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> <!-- Mobile Optimierung -->
    <title>Lagerverwaltung Sidebar</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>

<!-- Mobile Navbar -->
<nav class="navbar navbar-light bg-light fixed-top d-md-none">
    <div class="container-fluid">
        <!-- Offcanvas-Menü-Button -->
        <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar" aria-controls="offcanvasSidebar">
            ☰ Menü
        </button>
        <a class="navbar-brand ms-auto" href="#">Lagerverwaltung</a>
    </div>
</nav>

<!-- Sidebar für Desktop -->
<div class="sidebar d-none d-md-block">
    <div class="d-flex align-items-center pb-3 mb-3 link-dark text-decoration-none border-bottom">
        <span class="fs-5 fw-semibold">Lagerverwaltung</span>
    </div>
    <ul class="nav nav-pills flex-column mb-auto">
        <!-- Benutzeroptionen -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a href="../dashboard.php" class="nav-link link-dark">Dashboard</a>
            </li>
            <li>
                <a href="../inventory_movement.php" class="nav-link link-dark">Ein-/Ausbuchung</a>
            </li>
            <li>
                <a href="../product_groups/manage_product_groups.php" class="nav-link link-dark">Produktgruppen verwalten</a>
            </li>
            <li>
                <a href="../brands/manage_brands.php" class="nav-link link-dark">Marken verwalten</a>
            </li>
        <?php endif; ?>
    </ul>

    <hr>

    <!-- Admin-Bereich -->
    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin'): ?>
        <h6 class="text-uppercase">Admin-Bereich</h6>
        <ul class="nav nav-pills flex-column mb-auto">
            <li>
                <a href="../admin/user_management.php" class="nav-link link-dark">Benutzerverwaltung</a>
            </li>
            <li>
                <a href="../admin/manage_logs.php" class="nav-link link-dark">Logs anzeigen</a>
            </li>
        </ul>
    <?php endif; ?>

    <hr>

    <!-- Login/Logout -->
    <ul class="nav nav-pills flex-column">
        <?php if (isset($_SESSION['user_id'])): ?>
            <li>
                <a href="../logout.php" class="nav-link logout">Logout</a>
            </li>
        <?php else: ?>
            <li>
                <a href="../login.php" class="nav-link link-dark">Login</a>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- Offcanvas Sidebar für mobile Geräte -->
<div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="offcanvasSidebar" aria-labelledby="offcanvasSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Lagerverwaltung</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Schließen"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column mb-auto">
            <!-- Benutzeroptionen -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link link-dark">Dashboard</a>
                </li>
                <li>
                    <a href="../inventory_movement.php" class="nav-link link-dark">Ein-/Ausbuchung</a>
                </li>
                <li>
                    <a href="../product_groups/manage_product_groups.php" class="nav-link link-dark">Produktgruppen verwalten</a>
                </li>
                <li>
                    <a href="../brands/manage_brands.php" class="nav-link link-dark">Marken verwalten</a>
                </li>
            <?php endif; ?>
        </ul>

        <hr>

        <!-- Admin-Bereich -->
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'admin'): ?>
            <h6 class="text-uppercase">Admin-Bereich</h6>
            <ul class="nav nav-pills flex-column mb-auto">
                <li>
                    <a href="../admin/user_management.php" class="nav-link link-dark">Benutzerverwaltung</a>
                </li>
                <li>
                    <a href="../admin/logs.php" class="nav-link link-dark">Logs anzeigen</a>
                </li>
            </ul>
        <?php endif; ?>

        <hr>

        <!-- Login/Logout -->
        <ul class="nav nav-pills flex-column">
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="../logout.php" class="nav-link logout">Logout</a>
                </li>
            <?php else: ?>
                <li>
                    <a href="../login.php" class="nav-link link-dark">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
