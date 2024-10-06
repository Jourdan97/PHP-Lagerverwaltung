<?php
session_start();
include 'include/navbar.php'; // Sidebar einbinden

if (!isset($_SESSION['user_id'])) {
    die('Zugriff verweigert: Bitte loggen Sie sich ein.');
}

include 'include/db_connect.php';

// Produkte abrufen
$stmt = $pdo->query("SELECT * FROM products");
$products = $stmt->fetchAll();

// Verarbeiten der Ein-/Ausbuchung
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $movement_type = $_POST['movement_type'];
    $quantity = (int)$_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Menge aktualisieren basierend auf dem Bewegungstyp
    if ($movement_type == 'in') {
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    } elseif ($movement_type == 'out') {
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
        $stmt->execute([$quantity, $product_id]);
    }

    // Bewegungsdaten protokollieren
    $stmt = $pdo->prepare("INSERT INTO inventory_movements (product_id, user_id, movement_type, quantity) VALUES (?, ?, ?, ?)");
    $stmt->execute([$product_id, $user_id, $movement_type, $quantity]);

    echo "Bewegung erfolgreich gespeichert!";
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lagerverwaltung - Ein-/Ausbuchung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Ein-/Ausbuchung</h1>

    <!-- Formular zur Ein-/Ausbuchung -->
    <form method="POST" action="inventory_movement.php">
        <div class="mb-3">
            <label for="product_id" class="form-label">Produkt</label>
            <select class="form-select" id="product_id" name="product_id" required>
                <?php foreach ($products as $product): ?>
                    <option value="<?php echo $product['id']; ?>"><?php echo $product['name']; ?> (Bestand: <?php echo $product['quantity']; ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="movement_type" class="form-label">Bewegungstyp</label>
            <select class="form-select" id="movement_type" name="movement_type" required>
                <option value="in">Einbuchen</option>
                <option value="out">Ausbuchen</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="quantity" class="form-label">Menge</label>
            <input type="number" class="form-control" id="quantity" name="quantity" required min="1">
        </div>

        <button type="submit" class="btn btn-primary">Speichern</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>