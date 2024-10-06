<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php'; // Logging-Funktion einbinden
require_once '../include/auth.php'; // Authentifizierungsfunktionen einbinden

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

// Überprüfen der Anfrage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = trim($_POST['price']);
    $product_group_id = !empty($_POST['product_group_id']) ? (int)$_POST['product_group_id'] : null;
    $brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null; // Optional, falls benötigt

    // Validierung
    $errors = [];

    if (empty($name)) {
        $errors[] = 'Name ist erforderlich.';
    }

    if (!is_numeric($price) || $price < 0) {
        $errors[] = 'Preis muss eine positive Zahl sein.';
    }

    // Überprüfen, ob die angegebene Produktgruppe existiert (falls ausgewählt)
    if ($product_group_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_groups WHERE id = ?");
        $stmt->execute([$product_group_id]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = 'Die ausgewählte Produktgruppe existiert nicht.';
        }
    }

    // Überprüfen, ob die angegebene Marke existiert (falls ausgewählt)
    if ($brand_id !== null) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM brands WHERE id = ?");
        $stmt->execute([$brand_id]);
        if ($stmt->fetchColumn() == 0) {
            $errors[] = 'Die ausgewählte Marke existiert nicht.';
        }
    }

    // Optional: Überprüfen, ob die Menge gültig ist, falls sie bearbeitet werden soll
    if ($quantity !== null && $quantity < 0) {
        $errors[] = 'Menge darf nicht negativ sein.';
    }

    if (count($errors) === 0) {
        // Produkt aktualisieren
        $sql = "
            UPDATE products 
            SET name = :name, description = :description, price = :price, 
                product_group_id = :product_group_id, brand_id = :brand_id
        ";
        $params = [
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':product_group_id' => $product_group_id,
            ':brand_id' => $brand_id,
            ':id' => $id
        ];

        // Optional: Wenn die Menge aktualisiert werden soll
        if ($quantity !== null) {
            $sql .= ", quantity = :quantity ";
            $params[':quantity'] = $quantity;
        }

        $sql .= "WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        try {
            $stmt->execute($params);

            // Aktion protokollieren
            $action_description = "Produkt ID $id geändert: '$name'.";
            log_action($pdo, $_SESSION['user_id'], 'product', 'edit', $action_description);

            $_SESSION['message'] = 'Produkt erfolgreich aktualisiert.';
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Fehler beim Aktualisieren des Produkts: ' . $e->getMessage();
            $_SESSION['message_type'] = 'danger';
        }
    } else {
        $_SESSION['message'] = implode('<br>', $errors);
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
