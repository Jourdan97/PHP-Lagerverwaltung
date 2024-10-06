<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $product_group_id = (int)$_POST['product_group_id'];

    // Validierung
    if (empty($name)) {
        $_SESSION['message'] = 'Der Markenname darf nicht leer sein.';
        $_SESSION['message_type'] = 'danger';
    } elseif ($product_group_id <= 0) {
        $_SESSION['message'] = 'Bitte wählen Sie eine gültige Produktgruppe aus.';
        $_SESSION['message_type'] = 'danger';
    } else {
        // Prüfen, ob die Marke bereits existiert
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE name = ? AND product_group_id = ?");
        $stmt->execute([$name, $product_group_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['message'] = 'Diese Marke existiert bereits in der ausgewählten Produktgruppe.';
            $_SESSION['message_type'] = 'danger';
        } else {
            // Marke einfügen
            $stmt = $pdo->prepare("INSERT INTO brands (name, product_group_id) VALUES (?, ?)");
            try {
                $stmt->execute([$name, $product_group_id]);

                // Aktion protokollieren
                $action_description = "Marke '$name' hinzugefügt.";
                log_action($pdo, $_SESSION['user_id'], 'brand', 'add', $action_description);

                $_SESSION['message'] = 'Marke erfolgreich hinzugefügt.';
                $_SESSION['message_type'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['message'] = 'Fehler beim Hinzufügen der Marke: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
    }

    // Zurück zur Markenverwaltung
    header("Location: manage_brands.php");
    exit;
}
?>
