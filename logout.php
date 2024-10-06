<?php
session_start();

// Alle Session-Daten löschen
$_SESSION = array();

// Wenn das Session-Cookie existiert, es ebenfalls löschen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, 
        $params["path"], $params["domain"], 
        $params["secure"], $params["httponly"]
    );
}

// Die Session endgültig beenden
session_destroy();

// Zurück zur Login-Seite leiten
header("Location: login.php");
exit;

?>