<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['message'] = 'Produktgruppenname ist erforderlich.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Überprüfen, ob die Produktgruppe bereits existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_groups WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'Produktgruppe existiert bereits.';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Produktgruppe hinzufügen
            $stmt = $pdo->prepare("INSERT INTO product_groups (name) VALUES (?)");
            try {
                $stmt->execute([$name]);

                // Loggen der Aktion
                $action_description = "Neue Produktgruppe hinzugefügt: '$name'.";
                log_action($pdo, $_SESSION['user_id'], 'product_group', 'add', $action_description);

                $_SESSION['message'] = 'Produktgruppe erfolgreich hinzugefügt.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Hinzufügen der Produktgruppe: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    }

    // Zurück zur Produktgruppenverwaltung
    header("Location: manage_product_groups.php");
    exit;
}
?>
