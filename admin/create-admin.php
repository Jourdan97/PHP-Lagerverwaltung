<?php
// Pfad zur Datenbankverbindungsdatei anpassen, falls nötig
require_once '../include/db_connect.php';

// Funktion zur Sicherstellung, dass das Skript nur einmal ausgeführt wird
function isAdminCreated($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}

// Funktion zur Erstellung des Admin-Benutzers
function createAdmin($pdo) {
    $username = 'admin';
    $password = 'Air241103!'; // Du solltest ein sicheres Passwort wählen und dies später ändern
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $role = 'admin';

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':role' => $role
        ]);

        echo "<div style='color: green; font-weight: bold;'>Admin-Benutzer erfolgreich erstellt!</div>";
    } catch (PDOException $e) {
        // Überprüfe, ob der Benutzername bereits existiert
        if ($e->getCode() == 23000) { // SQLSTATE-Code für Integritätsverletzung
            echo "<div style='color: orange; font-weight: bold;'>Der Benutzername '$username' existiert bereits.</div>";
        } else {
            echo "<div style='color: red; font-weight: bold;'>Fehler beim Erstellen des Admin-Benutzers: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Sicherheitsmaßnahme: Passwortschutz für das Skript (optional)
// Du kannst diese Zeilen aktivieren und anpassen, um den Zugriff zu beschränken
/*
$protected = true;
$protectUser = 'setup';
$protectPass = 'your_secure_password';

if ($protected) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) ||
        $_SERVER['PHP_AUTH_USER'] !== $protectUser || $_SERVER['PHP_AUTH_PW'] !== $protectPass) {
        header('WWW-Authenticate: Basic realm="Admin Setup"');
        header('HTTP/1.0 401 Unauthorized');
        echo 'Zugang verweigert.';
        exit;
    }
}
*/

// Überprüfen, ob ein Admin bereits existiert
if (isAdminCreated($pdo)) {
    echo "<div style='color: blue; font-weight: bold;'>Ein Admin-Benutzer existiert bereits.</div>";
} else {
    // Admin-Benutzer erstellen
    createAdmin($pdo);
}

?>
