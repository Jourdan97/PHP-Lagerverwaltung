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
    $product_group_id = (int)$_POST['product_group_id'];

    // Validierung
    if ($id <= 0) {
        $_SESSION['message'] = 'Ungültige Marken-ID.';
        $_SESSION['message_type'] = 'danger';
    } elseif (empty($name)) {
        $_SESSION['message'] = 'Der Markenname darf nicht leer sein.';
        $_SESSION['message_type'] = 'danger';
    } elseif ($product_group_id <= 0) {
        $_SESSION['message'] = 'Bitte wählen Sie eine gültige Produktgruppe aus.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Prüfen, ob die Marke existiert
        $stmt = $pdo->prepare("SELECT * FROM brands WHERE id = ?");
        $stmt->execute([$id]);
        $brand = $stmt->fetch();

        if ($brand) {
            // Marke aktualisieren
            $stmt = $pdo->prepare("UPDATE brands SET name = ?, product_group_id = ? WHERE id = ?");
            try {
                $stmt->execute([$name, $product_group_id, $id]);

                // Aktion protokollieren
                $action_description = "Marke ID $id geändert zu '$name'.";
                log_action($pdo, $_SESSION['user_id'], 'brand', 'edit', $action_description);

                $_SESSION['message'] = 'Marke erfolgreich aktualisiert.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Aktualisieren der Marke: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'Marke existiert nicht.';
            $_SESSION['message_type'] = 'danger';
        }
    }

    // Zurück zur Markenverwaltung
    header("Location: manage_brands.php");
    exit;
}
?>
