<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
check_logged_in();
check_user_role('admin'); // Falls du eine Rollenverwaltung hast

// Suchparameter verarbeiten
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Abfrage erstellen
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (username LIKE ? OR email LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY username ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

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
    <title>Benutzer verwalten</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Externe CSS-Datei -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <?php include '../include/navbar.php'; ?>
    <div class="content mt-5">
        <h1 class="mb-4">Benutzer verwalten</h1>

        <!-- Nachricht anzeigen -->
        <?php if ($message != ''): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type ?? ''); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message ?? ''); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Schließen"></button>
            </div>
        <?php endif; ?>

        <!-- Suchformular -->
        <form method="GET" action="user_management.php" class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="search" class="form-label">Benutzer suchen</label>
                <input type="text" class="form-control" id="search" name="search" placeholder="Benutzername oder E-Mail" value="<?php echo htmlspecialchars($search ?? ''); ?>">
            </div>
            <div class="col-md-6 align-self-end">
                <button type="submit" class="btn btn-primary">Suchen</button>
                <a href="user_management.php" class="btn btn-secondary">Zurücksetzen</a>
            </div>
        </form>

        <!-- Benutzertabelle -->
        <table class="table table-bordered table-responsive">
            <thead>
                <tr>
                    <th>Benutzername</th>
                    <th>E-Mail</th>
                    <th>Rolle</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['role'] ?? ''); ?></td>
                            <td>
                                <!-- Bearbeiten-Button -->
                                <button class="btn btn-warning btn-sm me-1 btn-edit-user" 
                                        data-id="<?php echo $user['id']; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" 
                                        data-email="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                                        data-role="<?php echo htmlspecialchars($user['role'] ?? ''); ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editUserModal">
                                    Bearbeiten
                                </button>

                                <!-- Löschen-Button -->
                                <button class="btn btn-danger btn-sm btn-delete-user" 
                                        data-id="<?php echo $user['id']; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteUserModal">
                                    Löschen
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Keine Benutzer gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Button zum Hinzufügen eines neuen Benutzers -->
        <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#addUserModal">Neuen Benutzer hinzufügen</button>
    </div>
</div>

<!-- Modals -->

<!-- Modal zum Hinzufügen eines neuen Benutzers -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="add_user.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Neuen Benutzer hinzufügen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="add-username" class="form-label">Benutzername</label>
            <input type="text" class="form-control" id="add-username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="add-email" class="form-label">E-Mail</label>
            <input type="email" class="form-control" id="add-email" name="email"> <!-- E-Mail optional -->
          </div>
          <div class="mb-3">
            <label for="add-password" class="form-label">Passwort</label>
            <input type="password" class="form-control" id="add-password" name="password" required>
          </div>
          <div class="mb-3">
            <label for="add-role" class="form-label">Rolle</label>
            <select class="form-select" id="add-role" name="role" required>
                <option value="">Bitte auswählen</option>
                <option value="admin">Admin</option>
                <option value="user">Benutzer</option>
                <!-- Weitere Rollen nach Bedarf hinzufügen -->
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

<!-- Modal zum Bearbeiten eines Benutzers -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="edit_user.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Benutzer bearbeiten</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="edit-user-id">
          <div class="mb-3">
            <label for="edit-username" class="form-label">Benutzername</label>
            <input type="text" class="form-control" id="edit-username" name="username" required>
          </div>
          <div class="mb-3">
            <label for="edit-email" class="form-label">E-Mail</label>
            <input type="email" class="form-control" id="edit-email" name="email"> <!-- E-Mail optional -->
          </div>
          <div class="mb-3">
            <label for="edit-password" class="form-label">Passwort (leer lassen, wenn nicht geändert)</label>
            <input type="password" class="form-control" id="edit-password" name="password">
          </div>
          <div class="mb-3">
            <label for="edit-role" class="form-label">Rolle</label>
            <select class="form-select" id="edit-role" name="role" required>
                <option value="">Bitte auswählen</option>
                <option value="admin">Admin</option>
                <option value="user">Benutzer</option>
                <!-- Weitere Rollen nach Bedarf hinzufügen -->
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

<!-- Modal zum Löschen eines Benutzers -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="delete_user.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteUserModalLabel">Benutzer löschen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
        </div>
        <div class="modal-body">
          <p>Möchten Sie den Benutzer <strong id="delete-user-name"></strong> wirklich löschen?</p>
          <input type="hidden" name="id" id="delete-user-id">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
          <button type="submit" class="btn btn-danger">Löschen</button> <!-- Button rot -->
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap JavaScript Bundle (enthält Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Modal für Benutzer bearbeiten befüllen
    document.querySelectorAll('.btn-edit-user').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit-user-id').value = this.getAttribute('data-id');
            document.getElementById('edit-username').value = this.getAttribute('data-username');
            document.getElementById('edit-email').value = this.getAttribute('data-email');
            document.getElementById('edit-role').value = this.getAttribute('data-role');
            // Passwortfeld leeren
            document.getElementById('edit-password').value = '';
        });
    });

    // Modal für Benutzer löschen befüllen
    document.querySelectorAll('.btn-delete-user').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('delete-user-id').value = this.getAttribute('data-id');
            document.getElementById('delete-user-name').innerText = this.getAttribute('data-username');
        });
    });
</script>

</body>
</html>
