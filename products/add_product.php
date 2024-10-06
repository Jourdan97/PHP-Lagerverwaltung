<?php 
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php'; // Logging-Funktion einbinden
require_once '../include/auth.php'; // Authentifizierungsfunktionen einbinden

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

// Überprüfen, ob das Formular gesendet wurde
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../dashboard.php');
    exit;
}

// Funktion zum Generieren eines eindeutigen Codes
function generateUniqueCode($pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(code AS UNSIGNED)) FROM products");
    $maxCode = $stmt->fetchColumn();

    if ($maxCode === null || $maxCode < 7500) {
        return '7500';
    } else {
        return strval($maxCode + 1);
    }
}

// Formularfelder abrufen und bereinigen
$name = trim($_POST['name']);
$description = trim($_POST['description']);
$quantity = 0; // Menge wird standardmäßig auf 0 gesetzt
$price = trim($_POST['price']);
$product_group_id = !empty($_POST['product_group_id']) ? (int)$_POST['product_group_id'] : null;
$brand_id = !empty($_POST['brand_id']) ? (int)$_POST['brand_id'] : null;

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

if (count($errors) === 0) {
    // Artikelcode automatisch generieren
    $code = generateUniqueCode($pdo);

    // Produkt in die Datenbank einfügen
    $stmt = $pdo->prepare("
        INSERT INTO products (code, name, description, quantity, price, product_group_id, brand_id) 
        VALUES (:code, :name, :description, :quantity, :price, :product_group_id, :brand_id)
    ");
    try {
        $stmt->execute([
            ':code' => $code,
            ':name' => $name,
            ':description' => $description,
            ':quantity' => $quantity,
            ':price' => $price,
            ':product_group_id' => $product_group_id,
            ':brand_id' => $brand_id
        ]);

        // Loggen der Aktion
        $action_description = "Produkt '$name' mit Code $code hinzugefügt.";
        log_action($pdo, $_SESSION['user_id'], 'product', 'add', $action_description);

        // Erfolgsnachricht in die Session schreiben
        $_SESSION['message'] = 'Produkt erfolgreich hinzugefügt.';
        $_SESSION['message_type'] = 'success';

        // Zurück zum Dashboard
        header("Location: ../dashboard.php");
        exit;
    } catch (PDOException $e) {
        // Fehlerbehandlung
        $_SESSION['message'] = 'Fehler beim Hinzufügen des Produkts: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: ../dashboard.php");
        exit;
    }
} else {
    // Fehlernachrichten in die Session schreiben
    $_SESSION['message'] = implode('<br>', $errors);
    $_SESSION['message_type'] = 'danger';
    header("Location: ../dashboard.php");
    exit;
}
?>
