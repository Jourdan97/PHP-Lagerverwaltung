<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);

    if (empty($name)) {
        $_SESSION['message'] = 'Produktgruppenname ist erforderlich.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Überprüfen, ob die Produktgruppe existiert
        $stmt = $pdo->prepare("SELECT * FROM product_groups WHERE id = ?");
        $stmt->execute([$id]);
        $product_group = $stmt->fetch();

        if ($product_group) {
            // Überprüfen, ob der neue Name bereits existiert (außer bei diesem Produktgruppe)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_groups WHERE name = ? AND id != ?");
            $stmt->execute([$name, $id]);
            if ($stmt->fetchColumn() > 0) {
                $_SESSION['message'] = 'Eine andere Produktgruppe mit diesem Namen existiert bereits.';
                $_SESSION['message_type'] = 'danger';
            } else {
                // Produktgruppe aktualisieren
                $stmt = $pdo->prepare("UPDATE product_groups SET name = ? WHERE id = ?");
                try {
                    $stmt->execute([$name, $id]);

                    // Loggen der Aktion
                    $action_description = "Produktgruppe ID $id aktualisiert: '$name'.";
                    log_action($pdo, $_SESSION['user_id'], 'product_group', 'edit', $action_description);

                    $_SESSION['message'] = 'Produktgruppe erfolgreich aktualisiert.';
                    $_SESSION['message_type'] = 'success';
                } catch (PDOException $e) {
                    $_SESSION['message'] = 'Fehler beim Aktualisieren der Produktgruppe: ' . $e->getMessage();
                    $_SESSION['message_type'] = 'danger';
                }
            }
        } else {
            $_SESSION['message'] = 'Produktgruppe existiert nicht.';
            $_SESSION['message_type'] = 'danger';
        }
    }

    // Zurück zur Produktgruppenverwaltung
    header("Location: manage_product_groups.php");
    exit;
}
?>
