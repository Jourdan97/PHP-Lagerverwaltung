<?php
// /include/auth.php

/**
 * Überprüft, ob der Benutzer angemeldet ist.
 * Wenn nicht, wird der Benutzer zur Login-Seite weitergeleitet.
 */
function check_logged_in() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

/**
 * Überprüft, ob der Benutzer die erforderliche Rolle hat.
 * 
 * @param string $required_role Die erforderliche Benutzerrolle (z.B. 'admin').
 */
function check_user_role($required_role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $required_role) {
        die('Zugriff verweigert: Du hast nicht die erforderliche Berechtigung.');
    }
}
?>
