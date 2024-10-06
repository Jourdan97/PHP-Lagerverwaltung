<?php
session_start();
require_once '../include/db_connect.php';
require_once '../include/log_action.php';
require_once '../include/auth.php';

// Überprüfen, ob der Benutzer angemeldet ist und Admin-Rechte hat
check_logged_in();
check_user_role('admin');

// Funktion zum Abrufen aller Benutzer für das Filterformular
function getAllUsers($pdo) {
    $stmt = $pdo->prepare("SELECT id, username FROM users ORDER BY username ASC");
    $stmt->execute();
    return $stmt->fetchAll();
}

// Abrufen der Filterparameter aus GET-Anfragen
$userFilter = isset($_GET['user']) ? (int)$_GET['user'] : '';
$entityFilter = isset($_GET['entity']) ? $_GET['entity'] : '';
$operationFilter = isset($_GET['operation']) ? $_GET['operation'] : '';

// Aufbau der WHERE-Klausel basierend auf den Filtern
$whereClauses = [];
$params = [];

// Filter nach Benutzer
if (!empty($userFilter)) {
    $whereClauses[] = "u.id = ?";
    $params[] = $userFilter;
}

// Filter nach Entität
if (!empty($entityFilter)) {
    $whereClauses[] = "l.entity = ?";
    $params[] = $entityFilter;
}

// Filter nach Operation
if (!empty($operationFilter)) {
    $whereClauses[] = "l.operation = ?";
    $params[] = $operationFilter;
}

// Aufbau der finalen SQL-Abfrage
$sql = "
    SELECT 
        l.id,
        l.action,
        l.timestamp,
        u.username,
        l.entity,
        l.operation
    FROM 
        logs l
    JOIN 
        users u ON l.user_id = u.id
";

if (count($whereClauses) > 0) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$sql .= " ORDER BY l.timestamp DESC";

// Vorbereitung und Ausführung der SQL-Abfrage
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Abrufen aller Benutzer für das Filterformular
$users = getAllUsers($pdo);

// Definieren der möglichen Entitäten und Operationen
$entities = [
    '' => 'Alle Entitäten',
    'brand' => 'Marken',
    'product_group' => 'Produktgruppen',
    'product' => 'Produkte',
    'user' => 'Benutzer'
];

$operations = [
    '' => 'Alle Operationen',
    'add' => 'Hinzufügen',
    'delete' => 'Löschen',
    'edit' => 'Bearbeiten'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <!-- Kopfbereich -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs verwalten</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Externe CSS-Datei einbinden -->
    <link href="/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <?php include '../include/navbar.php'; ?>
    <div class="content mt-5">
        <h1 class="mb-4">Logs verwalten</h1>

        <!-- Such- und Filterformular -->
        <form method="GET" action="manage_logs.php" class="row g-3 mb-4">
            <!-- Benutzerfilter -->
            <div class="col-md-4">
                <label for="user" class="form-label">Benutzer</label>
                <select class="form-select" id="user" name="user">
                    <option value="">Alle Benutzer</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo htmlspecialchars($user['id']); ?>" <?php echo ($userFilter === (int)$user['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Entitätenfilter -->
            <div class="col-md-4">
                <label for="entity" class="form-label">Entität</label>
                <select class="form-select" id="entity" name="entity">
                    <?php foreach ($entities as $key => $value): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($entityFilter === $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($value); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Operationsfilter -->
            <div class="col-md-4">
                <label for="operation" class="form-label">Operation</label>
                <select class="form-select" id="operation" name="operation">
                    <?php foreach ($operations as $key => $value): ?>
                        <option value="<?php echo htmlspecialchars($key); ?>" <?php echo ($operationFilter === $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($value); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Absenden-Button -->
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtern</button>
                <a href="manage_logs.php" class="btn btn-secondary">Filter zurücksetzen</a>
            </div>
        </form>

        <!-- Anzeige der Logs -->
        <table class="table table-bordered table-responsive">
            <thead class="table-light">
                <tr>
                    <th>Timestamp</th>
                    <th>Benutzer</th>
                    <th>Entität</th>
                    <th>Operation</th>
                    <th>Aktion</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                            <td><?php echo htmlspecialchars($log['username']); ?></td>
                            <td><?php echo htmlspecialchars($entities[$log['entity']]); ?></td>
                            <td><?php echo htmlspecialchars($operations[$log['operation']]); ?></td>
                            <td><?php echo htmlspecialchars($log['action']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Keine Logs gefunden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div>
</div>

<!-- Bootstrap JavaScript Bundle (enthält Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
