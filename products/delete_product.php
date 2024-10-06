<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php'; // Logging-Funktion einbinden
require_once '../include/auth.php'; // Authentifizierungsfunktionen einbinden

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];

    // Überprüfen, ob das Produkt existiert
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        // Produkt löschen
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        try {
            $stmt->execute([$id]);

            // Loggen der Aktion
            $action_description = "Produkt ID $id gelöscht: '" . $product['name'] . "'.";
            log_action($pdo, $_SESSION['user_id'], 'product', 'delete', $action_description);

            $_SESSION['message'] = 'Produkt erfolgreich gelöscht.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Fehler beim Löschen des Produkts: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = 'Produkt existiert nicht.';
        $_SESSION['message_type'] = 'danger';
    }

    // Zurück zum Dashboard
    header("Location: ../dashboard.php");
    exit;
} else {
    // Ungültiger Zugriff
    $_SESSION['message'] = 'Ungültige Anfrage.';
    $_SESSION['message_type'] = 'danger';
    header("Location: ../dashboard.php");
    exit;
}
?>
