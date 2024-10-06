<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
check_logged_in();

// Produktgruppen für das Filter-Dropdown abrufen
$stmt = $pdo->query("SELECT id, name FROM product_groups ORDER BY name ASC");
$product_groups = $stmt->fetchAll();

// Such- und Filterparameter verarbeiten
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$product_group_filter = isset($_GET['product_group']) ? (int)$_GET['product_group'] : 0;

// Abfrage mit Filtern erstellen
$sql = "SELECT b.*, pg.name AS product_group_name FROM brands b
        LEFT JOIN product_groups pg ON b.product_group_id = pg.id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND b.name LIKE ?";
    $params[] = '%' . $search . '%';
}

if ($product_group_filter > 0) {
    $sql .= " AND b.product_group_id = ?";
    $params[] = $product_group_filter;
}

$sql .= " ORDER BY b.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$brands = $stmt->fetchAll();

// Nachrichten verarbeiten
$message = '';
$message_type = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Kopfbereich -->
    <meta charset="UTF-8">
    <title>Marken verwalten</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Externe CSS-Datei -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <?php include '../include/navbar.php'; ?>
    <div class="content mt-5">
        <h1 class="mb-4">Marken verwalten</h1>

        <!-- Nachricht anzeigen -->
        <?php if ($message != ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type ?? ''); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message ?? ''); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        <?php endif; ?>

        <!-- Filter- und Suchformular -->
        <form method="GET" action="manage_brands.php" class="row g-3 mb-4">
            <!-- Suchfeld -->
            <div class="col-md-4">
                <label for="search" class="form-label">Marke suchen</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Markenname" value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <!-- Produktgruppenfilter -->
            <div class="col-md-4">
                <label for="product_group" class="form-label">Produktgruppe</label>
                <select class="form-select" id="product_group" name="product_group">
                    <option value="0">Alle Produktgruppen</option>
                    <?php foreach ($product_groups as $pg): ?>
                        <option value="<?php echo $pg['id']; ?>" <?php echo ($product_group_filter == $pg['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($pg['name'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Absenden- und Zurücksetzen-Buttons -->
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Filtern</button>
                <a href="manage_brands.php" class="btn btn-secondary">Filter zurücksetzen</a>
            </div>
        </form>

        <!-- Markentabelle -->
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>Marke</th>
                    <th>Produktgruppe</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $brand): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($brand['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($brand['product_group_name'] ?? ''); ?></td>
                        <td>
                            <!-- Bearbeiten-Button -->
                            <button class="btn btn-warning btn-sm me-1 btn-edit-brand" 
                                    data-id="<?php echo $brand['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($brand['name'] ?? ''); ?>" 
                                    data-product-group-id="<?php echo $brand['product_group_id']; ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#editBrandModal">
                                Bearbeiten
                            </button>

                            <!-- Löschen-Button -->
                            <button class="btn btn-danger btn-sm btn-delete-brand" 
                                    data-id="<?php echo $brand['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($brand['name'] ?? ''); ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#deleteBrandModal">
                                Löschen
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Button zum Hinzufügen einer neuen Marke -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addBrandModal">Neue Marke hinzufügen</button>
    </div>
</div>

<!-- Modals -->
<!-- Modal zum Hinzufügen einer neuen Marke -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="add_brand.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addBrandModalLabel">Neue Marke hinzufügen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="brand-name" class="form-label">Markenname</label>
            <input type="text" class="form-control" id="brand-name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="brand-product-group" class="form-label">Produktgruppe</label>
            <select class="form-select" id="brand-product-group" name="product_group_id" required>
                <option value="">Bitte auswählen</option>
                <?php foreach ($product_groups as $pg): ?>
                    <option value="<?php echo $pg['id']; ?>"><?php echo htmlspecialchars($pg['name'] ?? ''); ?></option>
                <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Hinzufügen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal zum Bearbeiten einer Marke -->
<div class="modal fade" id="editBrandModal" tabindex="-1" aria-labelledby="editBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="edit_brand.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editBrandModalLabel">Marke bearbeiten</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-brand-id">
          <div class="mb-3">
            <label for="edit-brand-name" class="form-label">Markenname</label>
            <input type="text" class="form-control" id="edit-brand-name" name="name" required>
          </div>
          <div class="mb-3">
            <label for="edit-brand-product-group" class="form-label">Produktgruppe</label>
            <select class="form-select" id="edit-brand-product-group" name="product_group_id" required>
                <?php foreach ($product_groups as $pg): ?>
                    <option value="<?php echo $pg['id']; ?>"><?php echo htmlspecialchars($pg['name'] ?? ''); ?></option>
                <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Speichern</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal zum Löschen einer Marke -->
<div class="modal fade" id="deleteBrandModal" tabindex="-1" aria-labelledby="deleteBrandModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="delete_brand.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteBrandModalLabel">Marke löschen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <p>Möchten Sie die Marke <strong id="delete-brand-name"></strong> wirklich löschen?</p>
          <input type="hidden" name="id" id="delete-brand-id">
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Modal für Marke bearbeiten befüllen
    document.querySelectorAll('.btn-edit-brand').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit-brand-id').value = this.getAttribute('data-id');
            document.getElementById('edit-brand-name').value = this.getAttribute('data-name');
            document.getElementById('edit-brand-product-group').value = this.getAttribute('data-product-group-id');
        });
    });

    // Modal für Marke löschen befüllen
    document.querySelectorAll('.btn-delete-brand').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('delete-brand-id').value = this.getAttribute('data-id');
            document.getElementById('delete-brand-name').innerText = this.getAttribute('data-name');
        });
    });
</script>

</body>
</html>
