<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
check_logged_in();
check_user_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = trim($_POST['role']);

    // Validierung
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $_SESSION['message'] = 'Alle Felder sind erforderlich.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Überprüfen, ob der Benutzername oder die E-Mail bereits existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'Benutzername oder E-Mail existiert bereits.';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Passwort hashen
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Benutzer einfügen
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            try {
                $stmt->execute([$username, $email, $hashed_password, $role]);

                // Aktion protokollieren
                $action_description = "Neuer Benutzer hinzugefügt: '$username' mit Rolle '$role'.";
                log_action($pdo, $_SESSION['user_id'], 'user', 'add', $action_description);

                $_SESSION['message'] = 'Benutzer erfolgreich hinzugefügt.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Hinzufügen des Benutzers: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    }

    // Zurück zur Benutzerverwaltung
    header("Location: user_management.php");
    exit;
}
?>
