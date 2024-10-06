<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Produkt abrufen
    $stmt = $pdo->prepare("
        SELECT 
            p.id, 
            p.code, 
            p.name, 
            p.description, 
            p.price, 
            p.product_group_id, 
            p.brand_id
        FROM 
            products p
        WHERE 
            p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produkt nicht gefunden.']);
    }
    exit;
} else {
    echo json_encode(['success' => false, 'message' => 'Ungültige Anfrage.']);
    exit;
}
?>
