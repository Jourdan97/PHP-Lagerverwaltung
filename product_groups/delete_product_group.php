<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Überprüfen, ob die Produktgruppe existiert
    $stmt = $pdo->prepare("SELECT * FROM product_groups WHERE id = ?");
    $stmt->execute([$id]);
    $product_group = $stmt->fetch();

    if ($product_group) {
        // Überprüfen, ob die Produktgruppe mit Marken verknüpft ist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE product_group_id = ?");
        $stmt->execute([$id]);
        $brand_count = $stmt->fetchColumn();

        if ($brand_count > 0) {
            $_SESSION['message'] = 'Die Produktgruppe kann nicht gelöscht werden, da sie mit Marken verknüpft ist.';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Produktgruppe löschen
            $stmt = $pdo->prepare("DELETE FROM product_groups WHERE id = ?");
            try {
                $stmt->execute([$id]);

                // Loggen der Aktion
                $action_description = "Produktgruppe ID $id gelöscht: '" . $product_group['name'] . "'.";
                log_action($pdo, $_SESSION['user_id'], 'product_group', 'delete', $action_description);

                $_SESSION['message'] = 'Produktgruppe erfolgreich gelöscht.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Löschen der Produktgruppe: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    } else {
        $_SESSION['message'] = 'Produktgruppe existiert nicht.';
        $_SESSION['message_type'] = 'danger';
    }

    // Zurück zur Produktgruppenverwaltung
    header("Location: manage_product_groups.php");
    exit;
}
?>
