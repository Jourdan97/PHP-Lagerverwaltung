<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Überprüfen, ob die Marke existiert
    $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
    $stmt->execute([$id]);
    $brand = $stmt->fetch();

    if ($brand) {
        // Überprüfen, ob die Marke mit Produkten verknüpft ist
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE brand_id = ?");
        $stmt->execute([$id]);
        $product_count = $stmt->fetchColumn();

        if ($product_count > 0) {
            $_SESSION['message'] = 'Die Marke kann nicht gelöscht werden, da sie mit Produkten verknüpft ist.';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Marke löschen
            $stmt = $pdo->prepare("DELETE FROM brands WHERE id = ?");
            try {
                $stmt->execute([$id]);

                // Loggen der Aktion
                $action_description = "Marke ID $id gelöscht: '" . $brand['name'] . "'.";
                log_action($pdo, $_SESSION['user_id'], 'brand', 'delete', $action_description);

                $_SESSION['message'] = 'Marke erfolgreich gelöscht.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Löschen der Marke: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    } else {
        $_SESSION['message'] = 'Marke existiert nicht.';
        $_SESSION['message_type'] = 'danger';
    }

    // Zurück zur Markenverwaltung
    header("Location: manage_brands.php");
    exit;
}
?>
