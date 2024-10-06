<?php
// /include/log_action.php

/**
 * Protokolliert eine Aktion in der logs-Tabelle.
 *
 * @param PDO    $pdo         Die PDO-Datenbankverbindung.
 * @param int    $user_id     Die ID des Benutzers, der die Aktion durchgeführt hat.
 * @param string $entity      Die Entität, auf die sich die Aktion bezieht (z.B. 'brand', 'product_group', 'product', 'user').
 * @param string $operation   Die durchgeführte Operation (z.B. 'add', 'edit', 'delete').
 * @param string $description Eine detaillierte Beschreibung der Aktion.
 *
 * @return void
 */
function log_action($pdo, $user_id, $entity, $operation, $description) {
    try {
        $stmt = $pdo->prepare("INSERT INTO logs (user_id, entity, operation, action) VALUES (:user_id, :entity, :operation, :action)");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':entity', $entity, PDO::PARAM_STR);
        $stmt->bindParam(':operation', $operation, PDO::PARAM_STR);
        $stmt->bindParam(':action', $description, PDO::PARAM_STR);
        $stmt->execute();
    } catch (PDOException $e) {
        // Optional: Fehlerbehandlung, z.B. in eine Fehlerdatei schreiben
        error_log("Fehler beim Protokollieren der Aktion: " . $e->getMessage());
    }
}
?>
