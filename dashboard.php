<?php
session_start();
require_once 'include/db_connect.php';
require_once 'include/auth.php';
require_once 'include/log_action.php'; // Logging-Funktion einbinden

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

// Prüfen, ob eine Nachricht in der Session vorhanden ist
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type']; // 'success' oder 'danger'
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Produkte aus der Datenbank abrufen mit JOINs für Produktgruppe und Marke
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchQuery = '%' . $searchTerm . '%';

$stmt = $pdo->prepare("
    SELECT 
        p.id, 
        p.code, 
        p.name, 
        p.description, 
        p.quantity, 
        p.price, 
        pg.name AS product_group_name, 
        b.name AS brand_name,
        pg.id AS product_group_id,
        b.id AS brand_id
    FROM 
        products p
    LEFT JOIN 
        product_groups pg ON p.product_group_id = pg.id
    LEFT JOIN 
        brands b ON p.brand_id = b.id
    WHERE 
        p.name LIKE ?
    ORDER BY 
        p.name ASC
");
$stmt->execute([$searchQuery]);
$products = $stmt->fetchAll();

// Produktgruppen aus der Datenbank abrufen für das Hinzufügen und Bearbeiten von Produkten
$stmtGroups = $pdo->query("SELECT id, name FROM product_groups ORDER BY name ASC");
$product_groups = $stmtGroups->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Kopfbereich -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Produktverwaltung</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Externe CSS-Datei einbinden -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <?php include 'include/navbar.php'; ?>
    <div class="content mt-5">
        <h1 class="mb-4">Produktliste</h1>
        <!-- Suchfeld -->
        <div class="search-container">
            <form method="GET" action="dashboard.php" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Produkt suchen..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" class="btn btn-primary">Suchen</button>
            </form>
        </div>

        <!-- Anzeige der Nachricht -->
        <?php if ($message != ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show mt-3" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        <?php endif; ?>

        <!-- Tabelle für Produkte -->
        <table class="table table-bordered table-responsive mt-4">
            <thead class="table-light">
            <tr>
                <th>Artikelcode</th>
                <th>Name</th>
                <th>Beschreibung</th>
                <th>Produktgruppe</th>
                <th>Marke</th>
                <th>Menge</th>
                <th>Preis (pro Stück)</th>
                <th>Warenwert</th>
                <th>Aktionen</th>
            </tr>
            </thead>
            <tbody>
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $product):
                    // Berechnung des Warenwerts (Menge * Preis)
                    $warenwert = $product['quantity'] * $product['price'];
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($product['code']); ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                        <td><?php echo htmlspecialchars($product['product_group_name'] ?? 'Keine Zuordnung'); ?></td>
                        <td><?php echo htmlspecialchars($product['brand_name'] ?? 'Keine Zuordnung'); ?></td>
                        <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                        <td><?php echo number_format($product['price'], 2, ',', '.') . ' €'; ?></td>
                        <td><?php echo number_format($warenwert, 2, ',', '.') . ' €'; ?></td>
                        <td>
                            <!-- Bearbeiten Button -->
                            <button class="btn btn-warning btn-sm edit-btn" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editProductModal">Bearbeiten</button>
                            <!-- Löschen Button -->
                            <button class="btn btn-danger btn-sm delete-btn" 
                                    data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteProductModal">Löschen</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">Keine Produkte gefunden.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <!-- Button zum Hinzufügen eines neuen Produkts -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addProductModal">Neues Produkt hinzufügen</button>
    </div>
</div>

<!-- Modale für Hinzufügen, Bearbeiten und Löschen -->
<!-- Modal für Neues Produkt hinzufügen -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form action="/products/add_product.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="addProductModalLabel">Neues Produkt hinzufügen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <!-- Formularfelder -->
          <div class="mb-3">
            <label for="addName" class="form-label">Name</label>
            <input type="text" class="form-control" id="addName" name="name" required>
          </div>
          <div class="mb-3">
            <label for="addDescription" class="form-label">Beschreibung</label>
            <textarea class="form-control" id="addDescription" name="description" rows="3"></textarea>
          </div>
          <!-- Produktgruppe auswählen -->
          <div class="mb-3">
            <label for="addProductGroup" class="form-label">Produktgruppe</label>
            <select class="form-select" id="addProductGroup" name="product_group_id" required>
                <option value="">Bitte wählen...</option>
                <?php
                foreach ($product_groups as $group) {
                    echo '<option value="' . htmlspecialchars($group['id']) . '">' . htmlspecialchars($group['name']) . '</option>';
                }
                ?>
            </select>
          </div>
          <!-- Marke auswählen -->
          <div class="mb-3">
            <label for="addBrand" class="form-label">Marke</label>
            <select class="form-select" id="addBrand" name="brand_id" required>
                <option value="">Bitte wählen...</option>
                <!-- Optionen werden dynamisch über JavaScript hinzugefügt -->
            </select>
          </div>
          <!-- Menge wird nicht gesetzt und auf 0 gesetzt -->
          <input type="hidden" name="quantity" value="0">
          <div class="mb-3">
            <label for="addPrice" class="form-label">Preis (pro Stück in €)</label>
            <input type="number" step="0.01" class="form-control" id="addPrice" name="price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-success">Hinzufügen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal für Produkt bearbeiten -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="editProductForm" action="/products/edit_product.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="editProductModalLabel">Produkt bearbeiten</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <!-- Formularfelder -->
          <input type="hidden" id="editProductId" name="id">
          <div class="mb-3">
            <label for="editCode" class="form-label">Artikelcode</label>
            <input type="text" class="form-control" id="editCode" name="code" readonly>
          </div>
          <div class="mb-3">
            <label for="editName" class="form-label">Name</label>
            <input type="text" class="form-control" id="editName" name="name" required>
          </div>
          <div class="mb-3">
            <label for="editDescription" class="form-label">Beschreibung</label>
            <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
          </div>
          <!-- Produktgruppe auswählen -->
          <div class="mb-3">
            <label for="editProductGroup" class="form-label">Produktgruppe</label>
            <select class="form-select" id="editProductGroup" name="product_group_id" required>
                <option value="">Bitte wählen...</option>
                <?php
                foreach ($product_groups as $group) {
                    echo '<option value="' . htmlspecialchars($group['id']) . '">' . htmlspecialchars($group['name']) . '</option>';
                }
                ?>
            </select>
          </div>
          <!-- Marke auswählen -->
          <div class="mb-3">
            <label for="editBrand" class="form-label">Marke</label>
            <select class="form-select" id="editBrand" name="brand_id" required>
                <option value="">Bitte wählen...</option>
                <!-- Optionen werden dynamisch über JavaScript hinzugefügt -->
            </select>
          </div>
          <!-- Menge wird nicht bearbeitet und auf 0 gesetzt -->
          <input type="hidden" name="quantity" value="0">
          <div class="mb-3">
            <label for="editPrice" class="form-label">Preis (pro Stück in €)</label>
            <input type="number" step="0.01" class="form-control" id="editPrice" name="price" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-success">Speichern</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal für Produkt löschen -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="deleteProductForm" action="/products/delete_product.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteProductModalLabel">Produkt löschen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <p>Sind Sie sicher, dass Sie dieses Produkt löschen möchten?</p>
          <p><strong id="deleteProductName"></strong></p>
          <input type="hidden" id="deleteProductId" name="id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-danger">Löschen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JavaScript Bundle (enthält Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- Optional: jQuery (falls du es für andere Skripte benötigst) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- JavaScript zum Befüllen der Modals -->
<script>
    $(document).ready(function() {
        // Funktion zum Abrufen der Marken basierend auf der Produktgruppe
        function fetchBrands(productGroupId, callback) {
            $.ajax({
                url: '/products/get_brands.php',
                method: 'GET',
                data: { product_group_id: productGroupId },
                dataType: 'json',
                success: function(response) {
                    callback(response);
                },
                error: function() {
                    alert('Ein Fehler ist aufgetreten.');
                    callback({ success: false, message: 'Ein Fehler ist aufgetreten.' });
                }
            });
        }

        // Aktualisieren der Marken-Dropdown beim Ändern der Produktgruppe im Add Modal
        $('#addProductGroup').on('change', function() {
            var productGroupId = $(this).val();
            var brandSelect = $('#addBrand');

            // Leeren der aktuellen Optionen
            brandSelect.html('<option value="">Bitte wählen...</option>');

            if (productGroupId !== '') {
                fetchBrands(productGroupId, function(response) {
                    if (response.success) {
                        $.each(response.brands, function(index, brand) {
                            brandSelect.append('<option value="' + brand.id + '">' + brand.name + '</option>');
                        });
                    } else {
                        alert(response.message);
                    }
                });
            }
        });

        // Bearbeiten-Button für Produkte
        $('.edit-btn').on('click', function() {
            var productId = $(this).data('id');

            // AJAX-Aufruf zum Abrufen der Produktdaten
            $.ajax({
                url: '/products/get_product.php',
                method: 'GET',
                data: { id: productId },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        var product = data.product;
                        $('#editProductId').val(product.id);
                        $('#editCode').val(product.code);
                        $('#editName').val(product.name);
                        $('#editDescription').val(product.description);
                        $('#editPrice').val(product.price);
                        $('#editProductGroup').val(product.product_group_id);

                        // Leere die Marken-Dropdown und lade die passenden Marken
                        var editBrand = $('#editBrand');
                        editBrand.html('<option value="">Bitte wählen...</option>');

                        if (product.product_group_id !== '') {
                            fetchBrands(product.product_group_id, function(response) {
                                if (response.success) {
                                    $.each(response.brands, function(index, brand) {
                                        editBrand.append('<option value="' + brand.id + '">' + brand.name + '</option>');
                                    });

                                    // Setze die Marke, nachdem die Dropdown gefüllt ist
                                    editBrand.val(product.brand_id);
                                } else {
                                    alert(response.message);
                                }
                            });
                        }
                    } else {
                        alert('Produktdaten konnten nicht abgerufen werden.');
                        $('#editProductModal').modal('hide');
                    }
                },
                error: function() {
                    alert('Ein Fehler ist aufgetreten.');
                    $('#editProductModal').modal('hide');
                }
            });
        });

        // Löschen-Button für Produkte
        $('.delete-btn').on('click', function() {
            var productId = $(this).data('id');
            var productName = $(this).data('name');

            $('#deleteProductId').val(productId);
            $('#deleteProductName').text(productName);
        });

        // Aktualisieren der Marken-Dropdown beim Ändern der Produktgruppe im Edit Modal
        $('#editProductGroup').on('change', function() {
            var productGroupId = $(this).val();
            var brandSelect = $('#editBrand');

            // Leeren der aktuellen Optionen
            brandSelect.html('<option value="">Bitte wählen...</option>');

            if (productGroupId !== '') {
                fetchBrands(productGroupId, function(response) {
                    if (response.success) {
                        $.each(response.brands, function(index, brand) {
                            brandSelect.append('<option value="' + brand.id + '">' + brand.name + '</option>');
                        });
                    } else {
                        alert(response.message);
                    }
                });
            }
        });
    });
</script>
</body>
</html>
