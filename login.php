<?php
session_start();
include 'include/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $session_duration = $_POST['session_duration']; // Dauer der Session aus dem Formular

    // Benutzer aus der Datenbank abrufen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Session-Lebensdauer basierend auf der Auswahl setzen
        $session_lifetimes = [
            '30min' => 1800, // 30 Minuten in Sekunden
            '60min' => 3600, // 1 Stunde in Sekunden
            '2h' => 7200,    // 2 Stunden in Sekunden
            '6h' => 21600    // 6 Stunden in Sekunden
        ];

        $lifetime = $session_lifetimes[$session_duration];
        ini_set('session.cookie_lifetime', $lifetime); // Setze die Lebensdauer des Session-Cookies

        // Benutzer in der Session speichern
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: dashboard.php"); // Weiterleitung zum Dashboard
        exit;
    } else {
        $error = 'Ungültiger Benutzername oder Passwort.';
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lagerverwaltung Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f0f0f5; /* Dezenter Hintergrund */
        }
        .login-container {
            width: 100%;
            max-width: 380px; /* Ähnlich wie iCloud */
            padding: 40px;
            background-color: white;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1); /* Leichter Schatten */
            border-radius: 12px;
            text-align: center;
        }
        .login-container h3 {
            font-weight: 500;
            color: #1d1d1f; /* Dunkles Grau */
            margin-bottom: 30px;
        }
        .form-label {
            display: none; /* Eingabelabels ausgeblendet */
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #d1d1d6;
            padding: 15px;
            font-size: 16px;
            background-color: #f9f9f9;
            box-shadow: none;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #0071e3; /* Blau für Fokus */
        }
        .btn-primary {
            background-color: #0071e3;
            border: none;
            padding: 12px;
            font-size: 16px;
            border-radius: 8px;
            margin-top: 20px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #005bb5;
        }
        .alert {
            border-radius: 8px;
            padding: 10px;
        }
        .session-duration {
            margin-top: 20px;
        }
        .session-duration select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #d1d1d6;
            background-color: #f9f9f9;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h3>Anmelden zur Lagerverwaltung</h3>
    <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="mb-3">
            <input type="text" class="form-control" id="username" name="username" placeholder="Benutzername" required>
        </div>
        <div class="mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Passwort" required>
        </div>
        <div class="session-duration">
            <select id="session_duration" name="session_duration" required>
                <option value="30min">30 Minuten</option>
                <option value="60min">1 Stunde</option>
                <option value="2h">2 Stunden</option>
                <option value="6h">6 Stunden</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Anmelden</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>