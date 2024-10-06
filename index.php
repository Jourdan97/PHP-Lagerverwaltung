<?php
session_start();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lagerverwaltung</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Willkommen zur Lagerverwaltung</h1>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p>Sie sind eingeloggt.</p>
        <a href="dashboard.php" class="btn btn-primary">Zum Dashboard</a>
    <?php else: ?>
        <p>Bitte loggen Sie sich ein, um fortzufahren.</p>
        <a href="login.php" class="btn btn-primary">Login</a>
    <?php endif; ?>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>