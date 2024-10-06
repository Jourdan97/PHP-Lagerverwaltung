<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist
check_logged_in();

// Suchparameter verarbeiten
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Abfrage erstellen
$sql = "SELECT pg.*, GROUP_CONCAT(b.name SEPARATOR ', ') AS brands FROM product_groups pg
        LEFT JOIN brands b ON pg.id = b.product_group_id
        WHERE 1=1";

$params = [];

if ($search !== '') {
    $sql .= " AND pg.name LIKE ?";
    $params[] = '%' . $search . '%';
}

$sql .= " GROUP BY pg.id ORDER BY pg.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$product_groups = $stmt->fetchAll();

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
    <title>Produktgruppen verwalten</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Externe CSS-Datei -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <?php include '../include/navbar.php'; ?>
    <div class="content mt-5">
        <h1 class="mb-4">Produktgruppen verwalten</h1>

        <!-- Nachricht anzeigen -->
        <?php if ($message != ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type ?? ''); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message ?? ''); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        <?php endif; ?>

        <!-- Suchformular -->
        <form method="GET" action="manage_product_group.php" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" class="form-control" id="search" name="search" placeholder="Produktgruppenname" value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="col-md-4 align-self-end">
                <button type="submit" class="btn btn-primary">Suchen</button>
                <a href="manage_product_group.php" class="btn btn-secondary">Zurücksetzen</a>
            </div>
        </form>

        <!-- Produktgruppentabelle -->
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>Produktgruppe</th>
                    <th>Zugehörige Marken</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($product_groups as $pg): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pg['name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($pg['brands'] ?? ''); ?></td>
                        <td>
                            <!-- Bearbeiten-Button -->
                            <button class="btn btn-warning btn-sm me-1 btn-edit-pg" data-id="<?php echo $pg['id']; ?>" data-name="<?php echo htmlspecialchars($pg['name'] ?? ''); ?>" data-bs-toggle="modal" data-bs-target="#editPGModal">Bearbeiten</button>

                            <!-- Löschen-Button -->
                            <button class="btn btn-danger btn-sm btn-delete-pg" data-id="<?php echo $pg['id']; ?>" data-name="<?php echo htmlspecialchars($pg['name'] ?? ''); ?>" data-bs-toggle="modal" data-bs-target="#deletePGModal">Löschen</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Button zum Hinzufügen einer neuen Produktgruppe -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addPGModal">Neue Produktgruppe hinzufügen</button>
    </div>
</div>

<!-- Modals -->
<!-- Modal zum Hinzufügen einer neuen Produktgruppe -->
<div class="modal fade" id="addPGModal" tabindex="-1" aria-labelledby="addPGModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="add_product_group.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addPGModalLabel">Neue Produktgruppe hinzufügen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="pg-name" class="form-label">Produktgruppenname</label>
            <input type="text" class="form-control" id="pg-name" name="name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Hinzufügen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal zum Bearbeiten einer Produktgruppe -->
<div class="modal fade" id="editPGModal" tabindex="-1" aria-labelledby="editPGModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="edit_product_group.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editPGModalLabel">Produktgruppe bearbeiten</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-pg-id">
          <div class="mb-3">
            <label for="edit-pg-name" class="form-label">Produktgruppenname</label>
            <input type="text" class="form-control" id="edit-pg-name" name="name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Speichern</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal zum Löschen einer Produktgruppe -->
<div class="modal fade" id="deletePGModal" tabindex="-1" aria-labelledby="deletePGModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="delete_product_group.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="deletePGModalLabel">Produktgruppe löschen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <p>Möchten Sie die Produktgruppe <strong id="delete-pg-name"></strong> wirklich löschen?</p>
          <input type="hidden" name="id" id="delete-pg-id">
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
    // Modal für Produktgruppe bearbeiten befüllen
    document.querySelectorAll('.btn-edit-pg').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit-pg-id').value = this.getAttribute('data-id');
            document.getElementById('edit-pg-name').value = this.getAttribute('data-name');
        });
    });

    // Modal für Produktgruppe löschen befüllen
    document.querySelectorAll('.btn-delete-pg').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('delete-pg-id').value = this.getAttribute('data-id');
            document.getElementById('delete-pg-name').innerText = this.getAttribute('data-name');
        });
    });
</script>

</body>
</html>
