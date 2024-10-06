<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['product_group_id'])) {
    $product_group_id = (int)$_GET['product_group_id'];

    // Überprüfen, ob die Produktgruppe existiert
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_groups WHERE id = ?");
    $stmt->execute([$product_group_id]);
    if ($stmt->fetchColumn() == 0) {
        echo json_encode(['success' => false, 'message' => 'Produktgruppe existiert nicht.']);
        exit;
    }

    // Marken abrufen, die zur angegebenen Produktgruppe gehören
    // Annahme: Die Tabelle 'brands' hat eine Spalte 'product_group_id'
    $stmt = $pdo->prepare("SELECT id, name FROM brands WHERE product_group_id = ? ORDER BY name ASC");
    $stmt->execute([$product_group_id]);
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'brands' => $brands]);
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
    exit;
}
?>
