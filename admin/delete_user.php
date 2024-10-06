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

    // Validierung
    if ($id <= 0) {
        $_SESSION['message'] = 'Ungültige Benutzer-ID.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Überprüfen, ob der Benutzer existiert
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if ($user) {
            // Benutzer löschen
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            try {
                $stmt->execute([$id]);

                // Aktion protokollieren
                $action_description = "Benutzer gelöscht: '{$user['username']}' (ID: $id).";
                log_action($pdo, $_SESSION['user_id'], 'user', 'delete', $action_description);

                $_SESSION['message'] = 'Benutzer erfolgreich gelöscht.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Löschen des Benutzers: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
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
