<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
check_logged_in();
check_user_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password']; // Optional
    $role = trim($_POST['role']);

    // Validierung: E-Mail nicht mehr erforderlich
    if (empty($username) || empty($role)) {
        $_SESSION['message'] = 'Benutzername und Rolle sind erforderlich.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Überprüfen, ob der Benutzer existiert
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            // Überprüfen, ob der neue Benutzername oder die E-Mail bereits existiert (außer bei diesem Benutzer)
            if (!empty($email)) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE (username = ? OR email = ?) AND id != ?");
                $stmt->execute([$username, $email, $id]);
            } else {
                // Wenn E-Mail leer ist, nur den Benutzernamen prüfen
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $id]);
            }

            if ($stmt->fetchColumn() > 0) {
                $_SESSION['message'] = 'Benutzername oder E-Mail existiert bereits.';
                $_SESSION['message_type'] = 'danger';
            } else {
                // Passwort aktualisieren, wenn es nicht leer ist
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET username = ?, email = ?, password = ?, role = ? WHERE id = ?";
                    $params = [$username, $email ?: NULL, $hashed_password, $role, $id];
                } else {
                    $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                    $params = [$username, $email ?: NULL, $role, $id];
                }

                // Benutzer aktualisieren
                $stmt = $pdo->prepare($sql);
                try {
                    $stmt->execute($params);

                    // Aktion protokollieren
                    $action_description = "Benutzer ID $id aktualisiert: '$username' mit Rolle '$role'.";
                    log_action($pdo, $_SESSION['user_id'], 'user', 'edit', $action_description);

                    $_SESSION['message'] = 'Benutzer erfolgreich aktualisiert.';
                    $_SESSION['message_type'] = 'success';
                } catch (PDOException $e) {
                    $_SESSION['message'] = 'Fehler beim Aktualisieren des Benutzers: ' . $e->getMessage();
                    $_SESSION['message_type'] = 'danger';
                }
            }
        } else {
            $_SESSION['message'] = 'Benutzer existiert nicht.';
            $_SESSION['message_type'] = 'danger';
        }
    }

    // Zurück zur Benutzerverwaltung
    header("Location: user_management.php");
    exit;
}
?>
